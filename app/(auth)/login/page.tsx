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
  email: z
    .string()
    .min(1, "Email is required")
    .refine((val) => val.includes("@") && val.includes("."), { message: "Invalid email format" }),
  password: z.string().min(1, "Password is required"),
});

type LoginForm = z.infer<typeof schema>;

export default function LoginPage() {
  const router = useRouter();
  const { login } = useAuth();
  const { register, handleSubmit, formState: { errors } } = useForm<LoginForm>({
    resolver: zodResolver(schema),
    defaultValues: {
      email: "a@b.c",
      password: "12345678",
    },
  });

  const mutation = useMutation({
    mutationFn: authService.login,
    onSuccess: (data) => {
      login(data.access_token, data.user);
      router.push("/dashboard");
    },
    onError: (error) => toast.error(getErrorMessage(error, "Login failed")),
  });

  return (
    <AuthCard title="Welcome back" subtitle="Log in to your QuantStock workspace.">
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
      <div className="flex flex-col items-center gap-2 pt-2 text-xs text-zinc-400">
        <div className="rounded-md bg-white/5 px-3 py-1.5 text-center text-zinc-300 border border-white/10">
          Default Credentials: <span className="font-mono text-emerald-400">a@b.c</span> / <span className="font-mono text-emerald-400">12345678</span>
        </div>
        <Link href="/forgot-password" className="hover:text-white mt-1">Forgot password?</Link>
      </div>
    </AuthCard>
  );
}
