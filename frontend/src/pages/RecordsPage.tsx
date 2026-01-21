import { useState, useCallback, useMemo, useRef, useEffect } from 'react';
import { AgGridReact } from 'ag-grid-react';
import type { ColDef, CellValueChangedEvent, GridReadyEvent, GridApi, PaginationChangedEvent, SortChangedEvent } from 'ag-grid-community';
// AG Grid styles - required for v31
import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-quartz.css';

import { useAuth } from '../hooks/useAuth';
import useRecords from '../hooks/useRecords';
import useDropdownOptions from '../hooks/useDropdownOptions';
import MultiSelectCellEditor from '../components/MultiSelectCellEditor';
import type { RecordRowData, RecordFormData } from '../types';

const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];
const DEFAULT_PAGE_SIZE = 10;

function RecordsPage() {
  const { canEdit } = useAuth();
  const { records, isLoading, error, pagination, fetchRecords, createRecord, updateRecord, deleteRecord, refetch } = useRecords();
  const { singleSelectOptions, multiSelectOptions, isLoading: optionsLoading } = useDropdownOptions();
  
  const gridRef = useRef<AgGridReact>(null);
  const [gridApi, setGridApi] = useState<GridApi | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  
  // Pagination state
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(DEFAULT_PAGE_SIZE);
  const [sortField, setSortField] = useState<string>('id');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('desc');

  // Fetch records when pagination/sort changes
  useEffect(() => {
    fetchRecords({
      page: currentPage,
      perPage: pageSize,
      sortField,
      sortOrder,
    });
  }, [fetchRecords, currentPage, pageSize, sortField, sortOrder]);

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
      refetch();
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
      // Go to first page to see the new record
      setCurrentPage(1);
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

  // Handle sort change from AG Grid
  const onSortChanged = useCallback((event: SortChangedEvent) => {
    const columnState = event.api.getColumnState();
    const sortedColumn = columnState.find(col => col.sort);
    
    if (sortedColumn) {
      // Map AG Grid column IDs to backend field names
      const fieldMap: Record<string, string> = {
        'id': 'id',
        'text_field': 'text_field',
        'created_at': 'created_at',
        'updated_at': 'updated_at',
      };
      
      const backendField = fieldMap[sortedColumn.colId];
      if (backendField) {
        setSortField(backendField);
        setSortOrder(sortedColumn.sort as 'asc' | 'desc');
        setCurrentPage(1); // Reset to first page on sort change
      }
    }
  }, []);

  // Column definitions - memoized for performance
  const columnDefs: ColDef<RecordRowData>[] = useMemo(() => {
    const cols: ColDef<RecordRowData>[] = [
      {
        headerName: 'ID',
        field: 'id',
        width: 80,
        editable: false,
        sortable: true,
        filter: false,
      },
      {
        headerName: 'Text Field',
        field: 'text_field',
        flex: 1,
        minWidth: 200,
        editable: canEdit,
        sortable: true,
        filter: false,
      },
      {
        headerName: 'Single Select',
        field: 'single_select_label',
        width: 180,
        editable: canEdit,
        sortable: false,
        filter: false,
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: () => ({
          values: singleSelectOptions.map(opt => opt.label),
        }),
      },
      {
        headerName: 'Multi Select',
        field: 'multi_select_labels',
        flex: 1,
        minWidth: 200,
        editable: canEdit,
        sortable: false,
        filter: false,
        cellEditor: MultiSelectCellEditor,
        cellEditorParams: () => ({
          options: multiSelectOptions,
        }),
        cellEditorPopup: true,
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
  }, [canEdit, singleSelectOptions, multiSelectOptions, handleDeleteRecord]);

  // Grid ready handler
  const onGridReady = useCallback((params: GridReadyEvent) => {
    setGridApi(params.api);
  }, []);

  // Default column definition - memoized
  const defaultColDef: ColDef = useMemo(() => ({
    resizable: true,
  }), []);

  // Calculate pagination info
  const totalPages = pagination?.last_page || 1;
  const totalRecords = pagination?.total || 0;
  const fromRecord = pagination?.from || 0;
  const toRecord = pagination?.to || 0;

  // Pagination handlers
  const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
    }
  };

  const handlePageSizeChange = (newSize: number) => {
    setPageSize(newSize);
    setCurrentPage(1); // Reset to first page
  };

  // Generate page numbers for pagination
  const getPageNumbers = () => {
    const pages: (number | string)[] = [];
    const maxVisible = 5;
    
    if (totalPages <= maxVisible) {
      for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
      if (currentPage <= 3) {
        for (let i = 1; i <= 4; i++) pages.push(i);
        pages.push('...');
        pages.push(totalPages);
      } else if (currentPage >= totalPages - 2) {
        pages.push(1);
        pages.push('...');
        for (let i = totalPages - 3; i <= totalPages; i++) pages.push(i);
      } else {
        pages.push(1);
        pages.push('...');
        for (let i = currentPage - 1; i <= currentPage + 1; i++) pages.push(i);
        pages.push('...');
        pages.push(totalPages);
      }
    }
    
    return pages;
  };

  // Initial loading state (before first fetch)
  if (optionsLoading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="flex flex-col items-center gap-4">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white" />
          <p className="text-white/70">Loading...</p>
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
            {totalRecords} total records
            {!canEdit && ' • Read-only mode'}
          </p>
        </div>
        
        <div className="flex items-center gap-3">
          {saveError && (
            <div className="px-3 py-1.5 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200">
              {saveError}
            </div>
          )}
          
          {(isSaving || isLoading) && (
            <div className="flex items-center gap-2 text-sm text-gray-500">
              <div className="w-4 h-4 border-2 border-gray-300 border-t-sky-600 rounded-full animate-spin" />
              {isSaving ? 'Saving...' : 'Loading...'}
            </div>
          )}

          <button
            onClick={refetch}
            disabled={isLoading}
            className="bg-white hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg border border-gray-300 transition-all duration-200 flex items-center gap-2 disabled:opacity-50"
            title="Refresh records"
          >
            <svg className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div className="ag-theme-quartz" style={{ height: 500, width: '100%' }}>
          <AgGridReact
            ref={gridRef}
            rowData={rowData}
            columnDefs={columnDefs}
            defaultColDef={defaultColDef}
            onGridReady={onGridReady}
            onCellValueChanged={onCellValueChanged}
            onSortChanged={onSortChanged}
            animateRows={true}
            rowSelection="single"
            suppressRowClickSelection={true}
            stopEditingWhenCellsLoseFocus={true}
            singleClickEdit={canEdit}
            loading={isLoading}
          />
        </div>
        
        {/* Custom Pagination Controls */}
        <div className="flex flex-wrap items-center justify-between gap-4 px-4 py-3 border-t border-gray-200 bg-gray-50">
          {/* Page size selector */}
          <div className="flex items-center gap-2 text-sm text-gray-600">
            <span>Show</span>
            <select
              value={pageSize}
              onChange={(e) => handlePageSizeChange(Number(e.target.value))}
              className="border border-gray-300 rounded-md px-2 py-1 bg-white focus:ring-2 focus:ring-sky-500 focus:border-transparent outline-none"
            >
              {PAGE_SIZE_OPTIONS.map(size => (
                <option key={size} value={size}>{size}</option>
              ))}
            </select>
            <span>per page</span>
          </div>

          {/* Record info */}
          <div className="text-sm text-gray-600">
            Showing {fromRecord} to {toRecord} of {totalRecords} records
          </div>

          {/* Page navigation */}
          <div className="flex items-center gap-1">
            {/* First page */}
            <button
              onClick={() => goToPage(1)}
              disabled={currentPage === 1}
              className="p-2 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              title="First page"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
              </svg>
            </button>

            {/* Previous page */}
            <button
              onClick={() => goToPage(currentPage - 1)}
              disabled={currentPage === 1}
              className="p-2 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              title="Previous page"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            {/* Page numbers */}
            {getPageNumbers().map((page, index) => (
              <button
                key={index}
                onClick={() => typeof page === 'number' && goToPage(page)}
                disabled={page === '...'}
                className={`min-w-[32px] h-8 px-2 rounded-md text-sm font-medium transition-colors ${
                  page === currentPage
                    ? 'bg-sky-600 text-white'
                    : page === '...'
                    ? 'cursor-default'
                    : 'hover:bg-gray-200'
                }`}
              >
                {page}
              </button>
            ))}

            {/* Next page */}
            <button
              onClick={() => goToPage(currentPage + 1)}
              disabled={currentPage === totalPages}
              className="p-2 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              title="Next page"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>

            {/* Last page */}
            <button
              onClick={() => goToPage(totalPages)}
              disabled={currentPage === totalPages}
              className="p-2 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              title="Last page"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 5l7 7-7 7M5 5l7 7-7 7" />
              </svg>
            </button>
          </div>
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
              Click column headers to sort (ID and Text Field)
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
