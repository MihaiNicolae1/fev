import { useState, useRef, useEffect, forwardRef, useImperativeHandle } from 'react';
import type { ICellEditorParams } from 'ag-grid-community';

interface MultiSelectOption {
  id: number;
  label: string;
}

interface MultiSelectCellEditorProps extends ICellEditorParams {
  options: MultiSelectOption[];
}

const MultiSelectCellEditor = forwardRef((props: MultiSelectCellEditorProps, ref) => {
  const { value, options } = props;
  
  // Parse initial selected labels
  const initialLabels = value ? value.split(', ').map((l: string) => l.trim()).filter(Boolean) : [];
  const initialIds = initialLabels
    .map((label: string) => options.find(opt => opt.label === label)?.id)
    .filter((id: number | undefined): id is number => id !== undefined);
  
  const [selectedIds, setSelectedIds] = useState<number[]>(initialIds);
  const containerRef = useRef<HTMLDivElement>(null);
  // Store latest selectedIds in a ref so getValue always returns current value
  const selectedIdsRef = useRef<number[]>(selectedIds);

  // Keep ref in sync with state
  useEffect(() => {
    selectedIdsRef.current = selectedIds;
  }, [selectedIds]);

  // Focus the container when mounted
  useEffect(() => {
    containerRef.current?.focus();
  }, []);

  // AG Grid calls this to get the final value when editing stops
  useImperativeHandle(ref, () => ({
    getValue() {
      const selectedLabels = selectedIdsRef.current
        .map(id => options.find(opt => opt.id === id)?.label)
        .filter(Boolean);
      return selectedLabels.join(', ');
    },
    isPopup() {
      return true;
    },
    getPopupPosition() {
      return 'under';
    },
  }));

  const handleToggle = (id: number) => {
    setSelectedIds(prev => 
      prev.includes(id) 
        ? prev.filter(x => x !== id)
        : [...prev, id]
    );
  };

  return (
    <div 
      ref={containerRef}
      className="bg-white border border-gray-200 rounded-lg shadow-lg min-w-[200px] overflow-hidden"
      tabIndex={0}
    >
      {/* Options list */}
      <div className="max-h-[240px] overflow-y-auto py-1">
        {options.map(option => (
          <label
            key={option.id}
            className="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <input
              type="checkbox"
              checked={selectedIds.includes(option.id)}
              onChange={() => handleToggle(option.id)}
              className="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500 cursor-pointer"
            />
            <span className="text-sm text-gray-700">{option.label}</span>
          </label>
        ))}
      </div>
      
      {/* Footer with selection count */}
      {selectedIds.length > 0 && (
        <div className="px-4 py-2 border-t border-gray-100 bg-gray-50">
          <span className="text-xs text-gray-500">
            {selectedIds.length} selected
          </span>
        </div>
      )}
    </div>
  );
});

MultiSelectCellEditor.displayName = 'MultiSelectCellEditor';

export default MultiSelectCellEditor;
