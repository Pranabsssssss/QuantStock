"use client";

import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { ChatBubble } from "@/components/chat/chat-bubble";
import { EmptyState } from "@/components/ui/empty-state";
import { GlassCard } from "@/components/ui/glass-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { PageHeading } from "@/components/ui/page-heading";
import { ProgressRing } from "@/components/ui/progress-ring";
import { getErrorMessage } from "@/lib/error-message";
import { businessService } from "@/services/business.service";
import { toast } from "sonner";

interface InterviewMessage {
  role: "assistant" | "user";
  content: string;
}

export default function BusinessInterviewPage() {
  const [sessionId, setSessionId] = useState("");
  const [messages, setMessages] = useState<InterviewMessage[]>([]);
  const [messageInput, setMessageInput] = useState("");
  const [progress, setProgress] = useState(0);
  const [profilePreview, setProfilePreview] = useState<Record<string, unknown> | null>(null);

  const startMutation = useMutation({
    mutationFn: businessService.startInterview,
    onSuccess: (data) => {
      setSessionId(data.session_id);
      setMessages([{ role: "assistant", content: data.question }]);
      setProgress(0);
    },
    onError: (error) => toast.error(getErrorMessage(error, "Failed to start interview")),
  });

  const messageMutation = useMutation({
    mutationFn: businessService.sendInterviewMessage,
    onSuccess: (data, variables) => {
      setMessages((prev) => [...prev, { role: "user", content: variables.message }, ...(data.next_question ? [{ role: "assistant" as const, content: data.next_question }] : [])]);
      setProgress(data.progress ?? 0);
      setProfilePreview(data.profile ?? null);
      setMessageInput("");
    },
    onError: (error) => toast.error(getErrorMessage(error, "Failed to send interview message")),
  });

  const confirmMutation = useMutation({
    mutationFn: businessService.confirmInterview,
    onSuccess: () => toast.success("Interview confirmed"),
    onError: (error) => toast.error(getErrorMessage(error, "Failed to confirm interview")),
  });

  return (
    <div className="space-y-6">
      <PageHeading title="Business Discovery Interview" subtitle="AI-driven profile interview using backend interview endpoints." />
      {!sessionId ? (
        <GlassCard>
          <GradientButton loading={startMutation.isPending} onClick={() => startMutation.mutate()}>
            Start Interview
          </GradientButton>
        </GlassCard>
      ) : (
        <div className="grid gap-4 lg:grid-cols-[2fr_1fr]">
          <GlassCard className="space-y-4">
            <div className="max-h-[60vh] space-y-3 overflow-auto">
              {messages.map((message, index) => (
                <ChatBubble key={index} role={message.role === "assistant" ? "assistant" : "user"} content={message.content} />
              ))}
            </div>
            <div className="flex gap-2">
              <input value={messageInput} onChange={(event) => setMessageInput(event.target.value)} placeholder="Type your response..." className="flex-1 rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
              <GradientButton disabled={!messageInput.trim()} loading={messageMutation.isPending} onClick={() => messageMutation.mutate({ session_id: sessionId, message: messageInput })}>
                Send
              </GradientButton>
            </div>
            <GradientButton className="w-full" loading={confirmMutation.isPending} onClick={() => confirmMutation.mutate({ session_id: sessionId })}>
              Confirm Interview
            </GradientButton>
          </GlassCard>
          <GlassCard className="space-y-4">
            <h3 className="text-sm uppercase tracking-[0.2em] text-zinc-500">Progress</h3>
            <ProgressRing value={progress} />
            {!profilePreview ? <EmptyState title="No profile preview" description="Profile preview appears as backend returns interview profile data." /> : <pre className="overflow-auto rounded-xl bg-black/40 p-3 text-xs text-zinc-300">{JSON.stringify(profilePreview, null, 2)}</pre>}
          </GlassCard>
        </div>
      )}
    </div>
  );
}
