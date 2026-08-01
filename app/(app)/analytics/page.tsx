import { EmptyState } from "@/components/ui/empty-state";
import { PageHeading } from "@/components/ui/page-heading";

export default function AnalyticsPage() {
  return (
    <div className="space-y-6">
      <PageHeading title="Analytics" subtitle="Connected analytics views are pending backend analytics payload." />
      <EmptyState title="Analytics awaiting API response" description="Connect GET /analytics data to populate this module." />
    </div>
  );
}
