"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { AuthCard } from "@/components/ui/auth-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { authService } from "@/services/auth.service";
import { toast } from "sonner";
import { getErrorMessage } from "@/lib/error-message";

const schema = z.object({ email: z.string().email() });

export default function ForgotPasswordPage() {
  const { register, handleSubmit, formState: { errors } } = useForm<{ email: string }>({ resolver: zodResolver(schema) });

  const mutation = useMutation({
    mutationFn: authService.forgotPassword,
    onSuccess: () => toast.success("If your account exists, reset instructions were sent."),
    onError: (error) => toast.error(getErrorMessage(error, "Failed to send reset email")),
  });

  return (
    <AuthCard title="Forgot password" subtitle="Receive reset instructions by email.">
      <form onSubmit={handleSubmit((values) => mutation.mutate(values))} className="space-y-3">
        <div>
          <input {...register("email")} type="email" placeholder="Email" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.email ? <p className="mt-1 text-xs text-red-300">{errors.email.message}</p> : null}
        </div>
        <GradientButton type="submit" className="w-full" loading={mutation.isPending}>Send reset link</GradientButton>
      </form>
    </AuthCard>
  );
}
