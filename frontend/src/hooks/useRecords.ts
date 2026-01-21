import { useState, useEffect, useCallback } from 'react';
import recordsService from '../services/records';
import type { Record, RecordFormData } from '../types';

interface UseRecordsReturn {
  records: Record[];
  isLoading: boolean;
  error: string | null;
  totalRecords: number;
  refetch: () => Promise<void>;
  createRecord: (data: RecordFormData) => Promise<Record>;
  updateRecord: (id: number, data: Partial<RecordFormData>) => Promise<Record>;
  deleteRecord: (id: number) => Promise<void>;
}

export function useRecords(): UseRecordsReturn {
  const [records, setRecords] = useState<Record[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [totalRecords, setTotalRecords] = useState(0);

  const fetchRecords = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      const response = await recordsService.getRecords(1, 1000);
      setRecords(response.data);
      setTotalRecords(response.meta.total);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to fetch records';
      setError(message);
      console.error('Error fetching records:', err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchRecords();
  }, [fetchRecords]);

  const createRecord = useCallback(async (data: RecordFormData): Promise<Record> => {
    const newRecord = await recordsService.createRecord(data);
    setRecords(prev => [newRecord, ...prev]);
    setTotalRecords(prev => prev + 1);
    return newRecord;
  }, []);

  const updateRecord = useCallback(async (id: number, data: Partial<RecordFormData>): Promise<Record> => {
    const updatedRecord = await recordsService.updateRecord(id, data);
    setRecords(prev => prev.map(r => r.id === id ? updatedRecord : r));
    return updatedRecord;
  }, []);

  const deleteRecord = useCallback(async (id: number): Promise<void> => {
    await recordsService.deleteRecord(id);
    setRecords(prev => prev.filter(r => r.id !== id));
    setTotalRecords(prev => prev - 1);
  }, []);

  return {
    records,
    isLoading,
    error,
    totalRecords,
    refetch: fetchRecords,
    createRecord,
    updateRecord,
    deleteRecord,
  };
}

export default useRecords;
