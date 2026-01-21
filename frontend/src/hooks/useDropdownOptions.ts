import { useState, useEffect, useCallback } from 'react';
import dropdownOptionsService from '../services/dropdownOptions';
import type { DropdownOption } from '../types';

interface UseDropdownOptionsReturn {
  singleSelectOptions: DropdownOption[];
  multiSelectOptions: DropdownOption[];
  isLoading: boolean;
  error: string | null;
  refetch: () => Promise<void>;
}

export function useDropdownOptions(): UseDropdownOptionsReturn {
  const [singleSelectOptions, setSingleSelectOptions] = useState<DropdownOption[]>([]);
  const [multiSelectOptions, setMultiSelectOptions] = useState<DropdownOption[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchOptions = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      const response = await dropdownOptionsService.getDropdownOptions();
      setSingleSelectOptions(response.single_select);
      setMultiSelectOptions(response.multi_select);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to fetch dropdown options';
      setError(message);
      console.error('Error fetching dropdown options:', err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchOptions();
  }, [fetchOptions]);

  return {
    singleSelectOptions,
    multiSelectOptions,
    isLoading,
    error,
    refetch: fetchOptions,
  };
}

export default useDropdownOptions;
