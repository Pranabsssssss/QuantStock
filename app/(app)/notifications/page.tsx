"use client";

import { useQuery } from "@tanstack/react-query";
import { EmptyState } from "@/components/ui/empty-state";
import { ErrorState } from "@/components/ui/error-state";
import { GlassCard } from "@/components/ui/glass-card";
import { LoadingSkeleton } from "@/components/ui/loading-skeleton";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { notificationsService } from "@/services/notifications.service";

export default function NotificationsPage() {
  const notificationsQuery = useQuery({
    queryKey: ["notifications"],
    queryFn: notificationsService.list,
  });

  if (notificationsQuery.isLoading) return <LoadingSkeleton className="h-80" />;
  if (notificationsQuery.isError) return <ErrorState message={getErrorMessage(notificationsQuery.error)} onRetry={() => notificationsQuery.refetch()} />;

  const notifications = Array.isArray(notificationsQuery.data) ? notificationsQuery.data : [];

  return (
    <div className="space-y-6">
      <PageHeading title="Notification Center" subtitle="Business warnings and forecast alerts from backend." />
      {notifications.length === 0 ? (
        <EmptyState title="No notifications" description="Unread alerts from backend will appear here." />
      ) : (
        <div className="space-y-3">
          {notifications.map((item, index) => (
            <GlassCard key={index}>
              <pre className="text-xs text-zinc-300">{JSON.stringify(item, null, 2)}</pre>
            </GlassCard>
          ))}
        </div>
      )}
    </div>
  );
}
