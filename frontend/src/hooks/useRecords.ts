import { useState, useCallback, useRef } from 'react';
import recordsService, { GetRecordsParams } from '../services/records';
import type { Record, RecordFormData, PaginationMeta } from '../types';

interface UseRecordsReturn {
  records: Record[];
  isLoading: boolean;
  error: string | null;
  pagination: PaginationMeta | null;
  fetchRecords: (params?: GetRecordsParams) => Promise<void>;
  createRecord: (data: RecordFormData) => Promise<Record>;
  updateRecord: (id: number, data: Partial<RecordFormData>) => Promise<Record>;
  deleteRecord: (id: number) => Promise<void>;
  refetch: () => Promise<void>;
}

export function useRecords(): UseRecordsReturn {
  const [records, setRecords] = useState<Record[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pagination, setPagination] = useState<PaginationMeta | null>(null);
  
  // Store last params for refetch
  const lastParamsRef = useRef<GetRecordsParams>({});

  const fetchRecords = useCallback(async (params: GetRecordsParams = {}) => {
    setIsLoading(true);
    setError(null);
    lastParamsRef.current = params;
    
    try {
      const response = await recordsService.getRecords(params);
      setRecords(response.data);
      setPagination(response.meta);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to fetch records';
      setError(message);
      console.error('Error fetching records:', err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const refetch = useCallback(async () => {
    await fetchRecords(lastParamsRef.current);
  }, [fetchRecords]);

  const createRecord = useCallback(async (data: RecordFormData): Promise<Record> => {
    const newRecord = await recordsService.createRecord(data);
    // Refetch to update pagination correctly
    await refetch();
    return newRecord;
  }, [refetch]);

  const updateRecord = useCallback(async (id: number, data: Partial<RecordFormData>): Promise<Record> => {
    const updatedRecord = await recordsService.updateRecord(id, data);
    setRecords(prev => prev.map(r => r.id === id ? updatedRecord : r));
    return updatedRecord;
  }, []);

  const deleteRecord = useCallback(async (id: number): Promise<void> => {
    await recordsService.deleteRecord(id);
    // Refetch to update pagination correctly
    await refetch();
  }, [refetch]);

  return {
    records,
    isLoading,
    error,
    pagination,
    fetchRecords,
    createRecord,
    updateRecord,
    deleteRecord,
    refetch,
  };
}

export default useRecords;
