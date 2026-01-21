import api from './api';
import type { ApiResponse, PaginatedResponse, Record, RecordFormData } from '../types';

export const recordsService = {
  /**
   * Get all records with pagination
   */
  async getRecords(page = 1, perPage = 100): Promise<PaginatedResponse<Record>> {
    return api.get<PaginatedResponse<Record>>('/records', { page, per_page: perPage });
  },

  /**
   * Get a single record by ID
   */
  async getRecord(id: number): Promise<Record> {
    const response = await api.get<ApiResponse<Record>>(`/records/${id}`);
    
    if (response.success && response.data) {
      return response.data;
    }
    
    throw new Error(response.message || 'Failed to get record');
  },

  /**
   * Create a new record
   */
  async createRecord(data: RecordFormData): Promise<Record> {
    const response = await api.post<ApiResponse<Record>>('/records', data as unknown as globalThis.Record<string, unknown>);
    
    if (response.success && response.data) {
      return response.data;
    }
    
    throw new Error(response.message || 'Failed to create record');
  },

  /**
   * Update an existing record
   */
  async updateRecord(id: number, data: Partial<RecordFormData>): Promise<Record> {
    const response = await api.put<ApiResponse<Record>>(`/records/${id}`, data as unknown as globalThis.Record<string, unknown>);
    
    if (response.success && response.data) {
      return response.data;
    }
    
    throw new Error(response.message || 'Failed to update record');
  },

  /**
   * Delete a record
   */
  async deleteRecord(id: number): Promise<void> {
    const response = await api.delete<ApiResponse<null>>(`/records/${id}`);
    
    if (!response.success) {
      throw new Error(response.message || 'Failed to delete record');
    }
  },
};

export default recordsService;
