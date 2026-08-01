"use client";

import { useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { EmptyState } from "@/components/ui/empty-state";
import { GlassCard } from "@/components/ui/glass-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { voiceService } from "@/services/voice.service";
import { toast } from "sonner";

export default function VoicePage() {
  const [audioFile, setAudioFile] = useState<File | null>(null);
  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: async (file: File) => {
      const formData = new FormData();
      formData.append("audio", file);
      return voiceService.uploadVoice(formData);
    },
    onSuccess: async () => {
      toast.success("Voice request processed.");
      await queryClient.invalidateQueries({ queryKey: ["dashboard"] });
      await queryClient.invalidateQueries({ queryKey: ["dashboard-summary"] });
    },
    onError: (error) => toast.error(getErrorMessage(error, "Voice request failed")),
  });

  return (
    <div className="space-y-6">
      <PageHeading title="Voice" subtitle="Upload voice input to /voice and refresh dashboard automatically." />
      <GlassCard className="space-y-4">
        <input type="file" accept="audio/*" onChange={(event) => setAudioFile(event.target.files?.[0] ?? null)} className="w-full rounded-xl border border-dashed border-white/20 bg-black/30 px-4 py-10 text-sm" />
        <GradientButton loading={mutation.isPending} onClick={() => audioFile && mutation.mutate(audioFile)} disabled={!audioFile}>
          Process Voice
        </GradientButton>
      </GlassCard>
      {!mutation.data ? (
        <EmptyState title="No voice response yet" description="Upload audio to see backend processing results." />
      ) : (
        <GlassCard>
          <h3 className="text-lg font-medium">Voice Status</h3>
          <pre className="mt-3 overflow-auto rounded-xl bg-black/40 p-3 text-xs text-zinc-300">{JSON.stringify(mutation.data, null, 2)}</pre>
        </GlassCard>
      )}
    </div>
  );
}
