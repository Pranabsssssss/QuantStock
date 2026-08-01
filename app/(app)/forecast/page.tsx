import { EmptyState } from "@/components/ui/empty-state";
import { PageHeading } from "@/components/ui/page-heading";

export default function ForecastPage() {
  return (
    <div className="space-y-6">
      <PageHeading title="Forecast" subtitle="Forecast module attached to backend forecast outputs." />
      <EmptyState title="No forecast data" description="Forecast cards will render once /forecast responds." />
    </div>
  );
}
