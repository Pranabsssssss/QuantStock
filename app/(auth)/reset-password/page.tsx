"use client";

import { useSearchParams } from "next/navigation";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { AuthCard } from "@/components/ui/auth-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { authService } from "@/services/auth.service";
import { toast } from "sonner";
import { getErrorMessage } from "@/lib/error-message";

const schema = z.object({ password: z.string().min(6) });

export default function ResetPasswordPage() {
  const searchParams = useSearchParams();
  const token = searchParams.get("token") ?? "";
  const { register, handleSubmit, formState: { errors } } = useForm<{ password: string }>({ resolver: zodResolver(schema) });

  const mutation = useMutation({
    mutationFn: (payload: { token: string; password: string }) => authService.resetPassword(payload),
    onSuccess: () => toast.success("Password reset complete. Please log in."),
    onError: (error) => toast.error(getErrorMessage(error, "Reset failed")),
  });

  return (
    <AuthCard title="Reset password" subtitle="Set a new secure password.">
      <form onSubmit={handleSubmit((values) => mutation.mutate({ token, password: values.password }))} className="space-y-3">
        <div>
          <input {...register("password")} type="password" placeholder="New password" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.password ? <p className="mt-1 text-xs text-red-300">{errors.password.message}</p> : null}
        </div>
        <GradientButton type="submit" className="w-full" loading={mutation.isPending}>Update password</GradientButton>
      </form>
    </AuthCard>
  );
}
