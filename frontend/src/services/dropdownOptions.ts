import api from './api';
import type { ApiResponse, DropdownOptionsResponse, DropdownOption } from '../types';

export const dropdownOptionsService = {
  /**
   * Get all dropdown options grouped by type
   */
  async getDropdownOptions(): Promise<DropdownOptionsResponse> {
    const response = await api.get<ApiResponse<DropdownOptionsResponse>>('/dropdown-options');
    
    if (response.success && response.data) {
      return response.data;
    }
    
    throw new Error(response.message || 'Failed to get dropdown options');
  },

  /**
   * Get dropdown options by type
   */
  async getOptionsByType(type: 'single_select' | 'multi_select'): Promise<DropdownOption[]> {
    const response = await api.get<ApiResponse<DropdownOption[]>>(`/dropdown-options/${type}`);
    
    if (response.success && response.data) {
      return response.data;
    }
    
    throw new Error(response.message || 'Failed to get dropdown options');
  },
};

export default dropdownOptionsService;
