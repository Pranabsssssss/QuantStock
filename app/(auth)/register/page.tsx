"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { AuthCard } from "@/components/ui/auth-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { getErrorMessage } from "@/lib/error-message";
import { authService } from "@/services/auth.service";
import { toast } from "sonner";

const schema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  password: z.string().min(6),
});

type RegisterForm = z.infer<typeof schema>;

export default function RegisterPage() {
  const router = useRouter();
  const { register, handleSubmit, formState: { errors } } = useForm<RegisterForm>({ resolver: zodResolver(schema) });

  const mutation = useMutation({
    mutationFn: authService.register,
    onSuccess: () => {
      toast.success("Registration complete. Please log in.");
      router.push("/login");
    },
    onError: (error) => toast.error(getErrorMessage(error, "Registration failed")),
  });

  return (
    <AuthCard title="Create account" subtitle="Set up your QuantStock workspace.">
      <form className="space-y-3" onSubmit={handleSubmit((values) => mutation.mutate(values))}>
        <div>
          <input {...register("name")} placeholder="Full name" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.name ? <p className="mt-1 text-xs text-red-300">{errors.name.message}</p> : null}
        </div>
        <div>
          <input {...register("email")} type="email" placeholder="Email" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.email ? <p className="mt-1 text-xs text-red-300">{errors.email.message}</p> : null}
        </div>
        <div>
          <input {...register("password")} type="password" placeholder="Password" className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none" />
          {errors.password ? <p className="mt-1 text-xs text-red-300">{errors.password.message}</p> : null}
        </div>
        <GradientButton type="submit" className="w-full" loading={mutation.isPending}>Register</GradientButton>
      </form>
      <Link href="/login" className="text-xs text-zinc-400 hover:text-white">Already have an account? Login</Link>
    </AuthCard>
  );
}
