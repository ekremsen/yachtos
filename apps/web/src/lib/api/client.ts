export class ApiError extends Error {
  status: number;
  errors: Record<string, string[]>;

  constructor(status: number, errors: Record<string, string[]> = {}) {
    super(status === 401 ? "E-posta veya şifre hatalı; oturumunuz sona ermiş olabilir."
      : status === 403 ? "Hesabınızın organizasyon erişimi kullanılamıyor."
      : status === 422 ? "Lütfen form alanlarını kontrol edin."
      : status === 429 ? "Çok fazla deneme yaptınız. Bir dakika sonra tekrar deneyin."
      : "Sunucuya ulaşılamadı. Lütfen tekrar deneyin.");
    this.status = status;
    this.errors = errors && typeof errors === "object" ? Object.fromEntries(
      Object.entries(errors).filter(([, messages]) => Array.isArray(messages) && messages.every(message => typeof message === "string")),
    ) : {};
  }
}

export async function apiRequest<T>(path: string, options: {
  method?: "GET" | "POST";
  token?: string;
  body?: unknown;
} = {}): Promise<T> {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL;
  if (!baseUrl) throw new ApiError(0);
  let response: Response;
  try {
    response = await fetch(`${baseUrl.replace(/\/$/, "")}/api${path}`, {
      method: options.method ?? "GET",
      headers: {
        Accept: "application/json",
        ...(options.body !== undefined ? { "Content-Type": "application/json" } : {}),
        ...(options.token ? { Authorization: `Bearer ${options.token}` } : {}),
      },
      body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
      credentials: "omit",
      cache: "no-store",
      signal: AbortSignal.timeout(10000),
    });
  } catch {
    throw new ApiError(0);
  }
  if (!response.ok) {
    const payload = await response.json().catch(() => ({}));
    throw new ApiError(response.status, response.status === 422 ? payload?.errors : undefined);
  }
  if (response.status === 204) return undefined as T;
  try {
    return await response.json() as T;
  } catch {
    throw new ApiError(0);
  }
}
