"use client";

import { useQuery } from "@tanstack/react-query";
import { dashboardService } from "@/services/dashboard.service";

export const useDashboard = () =>
  useQuery({
    queryKey: ["dashboard"],
    queryFn: dashboardService.getDashboard,
  });

export const useDashboardSummary = () =>
  useQuery({
    queryKey: ["dashboard-summary"],
    queryFn: dashboardService.getSummary,
  });
