export type ApiStatus = "idle" | "loading" | "success" | "error";

export interface ApiErrorResponse {
  detail?: string;
  message?: string;
}

export interface User {
  id?: number;
  name?: string;
  email?: string;
  [key: string]: unknown;
}

export interface AuthResponse {
  access_token: string;
  user: User;
}

export interface RegisterResponse {
  success: boolean;
  user_id: number;
}

export interface DashboardSummaryWidget {
  key: string;
  label: string;
  value: string | number;
  trend?: string;
  status?: string;
}

export interface ChartPoint {
  label: string;
  value: number;
  secondaryValue?: number;
}

export interface DashboardResponse {
  widgets?: DashboardSummaryWidget[];
  alerts?: Array<Record<string, unknown>>;
  insights?: Array<Record<string, unknown>>;
  recent_activity?: Array<Record<string, unknown>>;
  sales_trend?: ChartPoint[];
  inventory_distribution?: ChartPoint[];
  [key: string]: unknown;
}

export interface InventoryItem {
  id: string | number;
  name: string;
  category?: string;
  stock?: number;
  min_stock?: number;
  status?: string;
  forecast_status?: string;
  price?: number;
  [key: string]: unknown;
}

export interface PaginatedInventory {
  items: InventoryItem[];
  page: number;
  total_pages: number;
  total: number;
}

export interface InterviewStartResponse {
  session_id: string;
  question: string;
}

export interface InterviewMessageRequest {
  session_id: string;
  message: string;
}

export interface InterviewMessageResponse {
  next_question?: string;
  profile?: Record<string, unknown>;
  progress?: number;
}

export interface UploadSummary {
  rows_imported?: number;
  rows_failed?: number;
  error_report_url?: string;
  [key: string]: unknown;
}

export interface ChatResponse {
  message?: string;
  references?: string[];
  [key: string]: unknown;
}

export interface VoiceResponse {
  success?: boolean;
  updated_products?: number;
  [key: string]: unknown;
}
