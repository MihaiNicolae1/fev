import { useState, useCallback, useMemo, useRef } from 'react';
import { AgGridReact } from 'ag-grid-react';
import type { ColDef, CellValueChangedEvent, GridReadyEvent, GridApi } from 'ag-grid-community';
// AG Grid styles - required for v31
import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-quartz.css';

import { useAuth } from '../hooks/useAuth';
import useRecords from '../hooks/useRecords';
import useDropdownOptions from '../hooks/useDropdownOptions';
import type { RecordRowData, RecordFormData } from '../types';

function RecordsPage() {
  const { canEdit } = useAuth();
  const { records, isLoading, error, createRecord, updateRecord, deleteRecord, refetch } = useRecords();
  const { singleSelectOptions, multiSelectOptions, isLoading: optionsLoading } = useDropdownOptions();
  
  const gridRef = useRef<AgGridReact>(null);
  const [, setGridApi] = useState<GridApi | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);

  // Transform records to row data format - memoized for performance
  const rowData: RecordRowData[] = useMemo(() => {
    return records.map(record => ({
      id: record.id,
      text_field: record.text_field,
      single_select_id: record.single_select_id,
      single_select_label: record.single_select?.label || '',
      multi_select_ids: record.multi_select_ids || [],
      multi_select_labels: record.multi_select_options?.map(o => o.label).join(', ') || '',
      created_by: record.created_by,
      creator_name: record.creator?.name || '',
      created_at: record.created_at,
      updated_at: record.updated_at,
    }));
  }, [records]);

  // Memoized dropdown values for cell editors
  const singleSelectValues = useMemo(() => {
    return singleSelectOptions.map(opt => opt.label);
  }, [singleSelectOptions]);

  const multiSelectValues = useMemo(() => {
    return multiSelectOptions.map(opt => opt.label);
  }, [multiSelectOptions]);

  // Helper functions for converting between labels and IDs
  const getSingleSelectIdFromLabel = useCallback((label: string): number | null => {
    const option = singleSelectOptions.find(opt => opt.label === label);
    return option?.id ?? null;
  }, [singleSelectOptions]);

  const getMultiSelectIdsFromLabels = useCallback((labelsString: string): number[] => {
    if (!labelsString) return [];
    const labels = labelsString.split(', ').map(l => l.trim()).filter(Boolean);
    return labels
      .map(label => multiSelectOptions.find(opt => opt.label === label)?.id)
      .filter((id): id is number => id !== undefined);
  }, [multiSelectOptions]);

  // Handle cell value change - auto-save on edit
  const onCellValueChanged = useCallback(async (event: CellValueChangedEvent<RecordRowData>) => {
    if (!canEdit || !event.data) return;

    const { data, colDef } = event;
    
    // Skip if this is a new row being added
    if (data.isNew) return;

    setIsSaving(true);
    setSaveError(null);

    try {
      const updateData: Partial<RecordFormData> = {};

      switch (colDef.field) {
        case 'text_field':
          updateData.text_field = data.text_field;
          break;
        case 'single_select_label':
          updateData.single_select_id = getSingleSelectIdFromLabel(data.single_select_label);
          break;
        case 'multi_select_labels':
          updateData.multi_select_ids = getMultiSelectIdsFromLabels(data.multi_select_labels);
          break;
      }

      await updateRecord(data.id, updateData);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to save changes';
      setSaveError(message);
      refetch(); // Refresh to revert changes
    } finally {
      setIsSaving(false);
    }
  }, [canEdit, getSingleSelectIdFromLabel, getMultiSelectIdsFromLabels, updateRecord, refetch]);

  // Add new record handler
  const handleAddRecord = useCallback(async () => {
    if (!canEdit) return;

    setIsSaving(true);
    setSaveError(null);

    try {
      const newRecordData: RecordFormData = {
        text_field: 'New Record',
        single_select_id: singleSelectOptions[0]?.id ?? null,
        multi_select_ids: [],
      };

      await createRecord(newRecordData);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to create record';
      setSaveError(message);
    } finally {
      setIsSaving(false);
    }
  }, [canEdit, singleSelectOptions, createRecord]);

  // Delete record handler
  const handleDeleteRecord = useCallback(async (id: number) => {
    if (!canEdit) return;
    
    if (!confirm('Are you sure you want to delete this record?')) return;

    setIsSaving(true);
    setSaveError(null);

    try {
      await deleteRecord(id);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to delete record';
      setSaveError(message);
    } finally {
      setIsSaving(false);
    }
  }, [canEdit, deleteRecord]);

  // Column definitions - memoized for performance
  const columnDefs: ColDef<RecordRowData>[] = useMemo(() => {
    const cols: ColDef<RecordRowData>[] = [
      {
        headerName: 'ID',
        field: 'id',
        width: 80,
        editable: false,
        sortable: true,
        filter: true,
      },
      {
        headerName: 'Text Field',
        field: 'text_field',
        flex: 1,
        minWidth: 200,
        editable: canEdit,
        sortable: true,
        filter: true,
      },
      {
        headerName: 'Single Select',
        field: 'single_select_label',
        width: 180,
        editable: canEdit,
        sortable: true,
        filter: true,
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: {
          values: singleSelectValues,
        },
      },
      {
        headerName: 'Multi Select',
        field: 'multi_select_labels',
        flex: 1,
        minWidth: 200,
        editable: canEdit,
        sortable: true,
        filter: true,
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: {
          values: ['', ...multiSelectValues, ...multiSelectValues.flatMap((val, i, arr) => 
            arr.slice(i + 1).map(other => `${val}, ${other}`)
          )].filter(Boolean),
        },
        valueFormatter: (params) => params.value || '',
      },
    ];

    // Add actions column for webadmin
    if (canEdit) {
      cols.push({
        headerName: 'Actions',
        width: 100,
        editable: false,
        sortable: false,
        filter: false,
        cellRenderer: (params: { data?: RecordRowData }) => {
          if (!params.data) return null;
          return (
            <button
              onClick={() => handleDeleteRecord(params.data!.id)}
              className="px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors"
              title="Delete record"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          );
        },
      });
    }

    return cols;
  }, [canEdit, singleSelectValues, multiSelectValues, handleDeleteRecord]);

  // Grid ready handler
  const onGridReady = useCallback((params: GridReadyEvent) => {
    setGridApi(params.api);
  }, []);

  // Default column definition - memoized
  const defaultColDef: ColDef = useMemo(() => ({
    resizable: true,
  }), []);

  // Loading state
  if (isLoading || optionsLoading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="flex flex-col items-center gap-4">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white" />
          <p className="text-white/70">Loading records...</p>
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-8 text-center">
        <div className="text-red-500 mb-4">
          <svg className="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 className="text-lg font-semibold text-gray-900 mb-2">Error Loading Records</h3>
        <p className="text-gray-600 mb-4">{error}</p>
        <button 
          onClick={refetch} 
          className="bg-sky-600 hover:bg-sky-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200"
        >
          Try Again
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-4 animate-[slideIn_0.4s_ease-out]">
      {/* Header */}
      <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-4 flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 className="text-xl font-semibold text-gray-900">Records</h2>
          <p className="text-sm text-gray-500 mt-1">
            {records.length} total records
            {!canEdit && ' • Read-only mode'}
          </p>
        </div>
        
        <div className="flex items-center gap-3">
          {saveError && (
            <div className="px-3 py-1.5 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200">
              {saveError}
            </div>
          )}
          
          {isSaving && (
            <div className="flex items-center gap-2 text-sm text-gray-500">
              <div className="w-4 h-4 border-2 border-gray-300 border-t-sky-600 rounded-full animate-spin" />
              Saving...
            </div>
          )}

          <button
            onClick={refetch}
            className="bg-white hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg border border-gray-300 transition-all duration-200 flex items-center gap-2"
            title="Refresh records"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>

          {canEdit && (
            <button
              onClick={handleAddRecord}
              disabled={isSaving}
              className="bg-sky-600 hover:bg-sky-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md flex items-center gap-2 disabled:opacity-50"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Add Record
            </button>
          )}
        </div>
      </div>

      {/* AG Grid Table */}
      <div className="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div className="ag-theme-quartz" style={{ height: 600, width: '100%' }}>
          <AgGridReact
            ref={gridRef}
            rowData={rowData}
            columnDefs={columnDefs}
            defaultColDef={defaultColDef}
            onGridReady={onGridReady}
            onCellValueChanged={onCellValueChanged}
            animateRows={true}
            rowSelection="single"
            suppressRowClickSelection={true}
            stopEditingWhenCellsLoseFocus={true}
            singleClickEdit={canEdit}
          />
        </div>
      </div>

      {/* Help text */}
      {canEdit && (
        <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-4">
          <h3 className="text-sm font-medium text-gray-700 mb-2">Quick Tips</h3>
          <ul className="text-xs text-gray-500 space-y-1">
            <li className="flex items-center gap-2">
              <span className="w-1.5 h-1.5 bg-sky-500 rounded-full" />
              Click on any cell to edit its value
            </li>
            <li className="flex items-center gap-2">
              <span className="w-1.5 h-1.5 bg-sky-500 rounded-full" />
              Use dropdowns to select single or multi-select options
            </li>
            <li className="flex items-center gap-2">
              <span className="w-1.5 h-1.5 bg-sky-500 rounded-full" />
              Changes are saved automatically when you click outside the cell
            </li>
          </ul>
        </div>
      )}
    </div>
  );
}

export default RecordsPage;
