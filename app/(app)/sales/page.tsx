import { EmptyState } from "@/components/ui/empty-state";
import { PageHeading } from "@/components/ui/page-heading";

export default function SalesPage() {
  return (
    <div className="space-y-6">
      <PageHeading title="Sales" subtitle="Sales operations integrated with backend sales services." />
      <EmptyState title="No sales data" description="Sales dashboards populate from API responses." />
    </div>
  );
}
