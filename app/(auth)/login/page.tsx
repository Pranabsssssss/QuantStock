"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { AuthCard } from "@/components/ui/auth-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { useAuth } from "@/contexts/auth-context";
import { getErrorMessage } from "@/lib/error-message";
import { authService } from "@/services/auth.service";
import { toast } from "sonner";

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
});

type LoginForm = z.infer<typeof schema>;

export default function LoginPage() {
  const router = useRouter();
  const { login } = useAuth();
  const { register, handleSubmit, formState: { errors } } = useForm<LoginForm>({ resolver: zodResolver(schema) });

  const mutation = useMutation({
    mutationFn: authService.login,
    onSuccess: (data) => {
      login(data.access_token, data.user);
      router.push("/dashboard");
    },
    onError: (error) => toast.error(getErrorMessage(error, "Login failed")),
  });

  return (
    <AuthCard title="Welcome back" subtitle="Log in to your AI business workspace.">
      <form className="space-y-3" onSubmit={handleSubmit((values) => mutation.mutate(values))}>
        <div>
          <input {...register("email")} type="email" placeholder="Email" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.email ? <p className="mt-1 text-xs text-red-300">{errors.email.message}</p> : null}
        </div>
        <div>
          <input {...register("password")} type="password" placeholder="Password" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.password ? <p className="mt-1 text-xs text-red-300">{errors.password.message}</p> : null}
        </div>
        <GradientButton type="submit" className="w-full" loading={mutation.isPending}>Login</GradientButton>
      </form>
      <div className="flex justify-between text-xs text-zinc-400">
        <Link href="/forgot-password" className="hover:text-white">Forgot password?</Link>
        <Link href="/register" className="hover:text-white">Create account</Link>
      </div>
    </AuthCard>
  );
}
