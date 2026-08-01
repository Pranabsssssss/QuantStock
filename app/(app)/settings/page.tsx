"use client";

import { useMutation, useQuery } from "@tanstack/react-query";
import { EmptyState } from "@/components/ui/empty-state";
import { ErrorState } from "@/components/ui/error-state";
import { GlassCard } from "@/components/ui/glass-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { LoadingSkeleton } from "@/components/ui/loading-skeleton";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { settingsService } from "@/services/settings.service";
import { toast } from "sonner";

export default function SettingsPage() {
  const settingsQuery = useQuery({ queryKey: ["settings"], queryFn: settingsService.getSettings });
  const updateMutation = useMutation({
    mutationFn: settingsService.updateSettings,
    onSuccess: () => toast.success("Settings updated"),
    onError: (error) => toast.error(getErrorMessage(error, "Failed to update settings")),
  });

  if (settingsQuery.isLoading) return <LoadingSkeleton className="h-80" />;
  if (settingsQuery.isError) return <ErrorState message={getErrorMessage(settingsQuery.error)} onRetry={() => settingsQuery.refetch()} />;

  return (
    <div className="space-y-6">
      <PageHeading title="Settings" subtitle="Manage profile, security, API status, and notifications." />
      {!settingsQuery.data ? (
        <EmptyState title="No settings data" description="Waiting for /settings response." />
      ) : (
        <GlassCard className="space-y-4">
          <pre className="overflow-auto rounded-xl bg-black/40 p-4 text-xs text-zinc-300">{JSON.stringify(settingsQuery.data, null, 2)}</pre>
          <GradientButton onClick={() => updateMutation.mutate(settingsQuery.data as Record<string, unknown>)} loading={updateMutation.isPending}>
            Save Settings
          </GradientButton>
        </GlassCard>
      )}
    </div>
  );
}
