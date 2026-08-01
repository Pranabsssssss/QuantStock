"use client";

import { useQueryClient } from "@tanstack/react-query";
import { DistributionChart } from "@/components/dashboard/distribution-chart";
import { MetricCard } from "@/components/dashboard/metric-card";
import { SalesChart } from "@/components/dashboard/sales-chart";
import { EmptyState } from "@/components/ui/empty-state";
import { ErrorState } from "@/components/ui/error-state";
import { GlassCard } from "@/components/ui/glass-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { LoadingSkeleton } from "@/components/ui/loading-skeleton";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { useDashboard, useDashboardSummary } from "@/hooks/use-dashboard";

export default function DashboardPage() {
  const queryClient = useQueryClient();
  const dashboardQuery = useDashboard();
  const summaryQuery = useDashboardSummary();

  if (dashboardQuery.isLoading) {
    return <div className="grid gap-4 xl:grid-cols-4">{Array.from({ length: 8 }).map((_, i) => <LoadingSkeleton key={i} className="h-32" />)}</div>;
  }

  if (dashboardQuery.isError) {
    return <ErrorState message={getErrorMessage(dashboardQuery.error)} onRetry={() => dashboardQuery.refetch()} />;
  }

  const data = dashboardQuery.data;
  const summaryData = summaryQuery.data;
  const widgets = (summaryData?.widgets ?? data?.widgets ?? []) as Array<{ key: string; label: string; value: string | number; trend?: string }>;

  return (
    <div className="space-y-6">
      <PageHeading title="Welcome back" subtitle="Here is your business performance summary from live backend data." />
      <div className="flex justify-end">
        <GradientButton
          loading={summaryQuery.isFetching}
          onClick={async () => {
            await queryClient.invalidateQueries({ queryKey: ["dashboard-summary"] });
          }}
        >
          Refresh Summary
        </GradientButton>
      </div>
      {widgets.length === 0 ? (
        <EmptyState title="No dashboard metrics yet" description="Metrics will appear after /dashboard or /dashboard/summary responds." />
      ) : (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          {widgets.map((item) => (
            <MetricCard key={item.key} label={item.label} value={item.value} trend={item.trend} />
          ))}
        </div>
      )}
      <div className="grid gap-4 lg:grid-cols-2">
        <GlassCard>
          <h3 className="mb-4 text-lg font-medium">Sales Trend</h3>
          {Array.isArray(data?.sales_trend) && data.sales_trend.length > 0 ? (
            <SalesChart data={data.sales_trend} />
          ) : (
            <EmptyState title="No trend data" description="Waiting for /dashboard sales trend payload." />
          )}
        </GlassCard>
        <GlassCard>
          <h3 className="mb-4 text-lg font-medium">Inventory Distribution</h3>
          {Array.isArray(data?.inventory_distribution) && data.inventory_distribution.length > 0 ? (
            <DistributionChart data={data.inventory_distribution} />
          ) : (
            <EmptyState title="No distribution data" description="Waiting for /dashboard inventory distribution payload." />
          )}
        </GlassCard>
      </div>
    </div>
  );
}
