import { EmptyState } from "@/components/ui/empty-state";
import { PageHeading } from "@/components/ui/page-heading";

export default function ReportsPage() {
  return (
    <div className="space-y-6">
      <PageHeading title="Reports" subtitle="Operational reports from backend reporting APIs." />
      <EmptyState title="No reports available" description="Report data appears once report endpoints are connected." />
    </div>
  );
}
