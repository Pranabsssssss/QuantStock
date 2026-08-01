"use client";

import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { EmptyState } from "@/components/ui/empty-state";
import { GlassCard } from "@/components/ui/glass-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { uploadService } from "@/services/upload.service";
import type { UploadSummary } from "@/types/api";
import { toast } from "sonner";

export default function UploadPage() {
  const [file, setFile] = useState<File | null>(null);
  const [progress, setProgress] = useState(0);

  const mutation = useMutation({
    mutationFn: async (csvFile: File) => {
      const formData = new FormData();
      formData.append("file", csvFile);
      return uploadService.uploadCsv(formData, setProgress);
    },
    onError: (error) => toast.error(getErrorMessage(error, "Upload failed")),
  });

  const summary = mutation.data as UploadSummary | undefined;

  return (
    <div className="space-y-6">
      <PageHeading title="CSV Upload" subtitle="Upload inventory CSV and review backend validation results." />
      <GlassCard className="space-y-4">
        <input
          type="file"
          accept=".csv"
          onChange={(event) => setFile(event.target.files?.[0] ?? null)}
          className="w-full rounded-xl border border-dashed border-white/20 bg-black/30 px-4 py-10 text-sm"
        />
        <GradientButton onClick={() => file && mutation.mutate(file)} disabled={!file} loading={mutation.isPending}>
          Upload CSV
        </GradientButton>
        {mutation.isPending ? <p className="text-sm text-zinc-400">Upload Progress: {progress}%</p> : null}
      </GlassCard>
      {!summary ? (
        <EmptyState title="No upload summary yet" description="Submit a CSV to receive import summary from /upload/csv." />
      ) : (
        <GlassCard>
          <h3 className="text-lg font-medium">Import Summary</h3>
          <p className="mt-2 text-sm text-zinc-300">Rows Imported: {summary.rows_imported ?? "-"}</p>
          <p className="text-sm text-zinc-300">Rows Failed: {summary.rows_failed ?? "-"}</p>
          {summary.error_report_url ? (
            <a href={summary.error_report_url} className="mt-2 inline-block text-sm text-lime-300 underline">
              Download Error Report
            </a>
          ) : null}
        </GlassCard>
      )}
    </div>
  );
}
