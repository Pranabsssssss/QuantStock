export const getErrorMessage = (error: unknown, fallback = "Something went wrong") => {
  if (typeof error === "string") return error;
  if (error && typeof error === "object") {
    const maybeMessage = (error as { message?: string }).message;
    if (maybeMessage) return maybeMessage;
    const maybeDetail = (error as { response?: { data?: { detail?: string; message?: string } } }).response?.data;
    if (maybeDetail?.detail) return maybeDetail.detail;
    if (maybeDetail?.message) return maybeDetail.message;
  }
  return fallback;
};
