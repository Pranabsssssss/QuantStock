"use client";

import { useQuery } from "@tanstack/react-query";
import { EmptyState } from "@/components/ui/empty-state";
import { ErrorState } from "@/components/ui/error-state";
import { GlassCard } from "@/components/ui/glass-card";
import { LoadingSkeleton } from "@/components/ui/loading-skeleton";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { businessService } from "@/services/business.service";

export default function BusinessProfilePage() {
  const profileQuery = useQuery({ queryKey: ["business-profile"], queryFn: businessService.getProfile });

  if (profileQuery.isLoading) return <LoadingSkeleton className="h-80" />;
  if (profileQuery.isError) return <ErrorState message={getErrorMessage(profileQuery.error)} onRetry={() => profileQuery.refetch()} />;

  const profile = profileQuery.data;

  return (
    <div className="space-y-6">
      <PageHeading title="Business Profile" subtitle="Live extracted profile from backend services." />
      {!profile || Object.keys(profile).length === 0 ? (
        <EmptyState title="No profile data" description="Complete the business interview to generate a profile." />
      ) : (
        <GlassCard>
          <pre className="overflow-auto rounded-xl bg-black/40 p-4 text-xs text-zinc-200">{JSON.stringify(profile, null, 2)}</pre>
        </GlassCard>
      )}
    </div>
  );
}
