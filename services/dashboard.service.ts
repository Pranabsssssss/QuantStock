import { apiClient } from "@/api/client";
import type { DashboardResponse } from "@/types/api";

export const dashboardService = {
  getDashboard() {
    return apiClient.get<DashboardResponse>("/dashboard").then((res) => res.data);
  },
  getSummary() {
    return apiClient.get<DashboardResponse>("/dashboard/summary").then((res) => res.data);
  },
};
