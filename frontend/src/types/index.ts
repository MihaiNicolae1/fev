// User types
export interface Role {
  id: number;
  name: string;
  slug: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  role: Role;
  can_edit: boolean;
  created_at: string;
  updated_at: string;
}

// Dropdown option types
export interface DropdownOption {
  id: number;
  type: 'single_select' | 'multi_select';
  label: string;
  value: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

// Record types
export interface RecordCreator {
  id: number;
  name: string;
}

export interface Record {
  id: number;
  text_field: string;
  single_select_id: number | null;
  single_select: DropdownOption | null;
  multi_select_ids: number[];
  multi_select_options: DropdownOption[];
  created_by: number;
  creator: RecordCreator;
  created_at: string;
  updated_at: string;
}

// API response types
export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface PaginatedResponse<T> {
  success: boolean;
  message: string;
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface LoginResponse {
  user: User;
  token: string;
  token_type: string;
}

export interface DropdownOptionsResponse {
  single_select: DropdownOption[];
  multi_select: DropdownOption[];
}

// Form types
export interface LoginFormData {
  email: string;
  password: string;
}

export interface RecordFormData {
  text_field: string;
  single_select_id: number | null;
  multi_select_ids: number[];
}

// AG Grid row data type
export interface RecordRowData {
  id: number;
  text_field: string;
  single_select_id: number | null;
  single_select_label: string;
  multi_select_ids: number[];
  multi_select_labels: string;
  created_by: number;
  creator_name: string;
  created_at: string;
  updated_at: string;
  isNew?: boolean;
}
