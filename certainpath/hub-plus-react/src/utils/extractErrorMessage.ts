import { AxiosResponse } from "axios";

export async function extractErrorMessage(
  errorResponse: AxiosResponse,
): Promise<string> {
  let errorMessage = "An error occurred";
  const contentType = errorResponse?.headers?.["content-type"] || "";

  if (
    errorResponse?.data instanceof Blob &&
    contentType.includes("application/json")
  ) {
    try {
      const text = await errorResponse.data.text();
      const jsonError = JSON.parse(text);

      if (jsonError.errors) {
        if (Array.isArray(jsonError.errors)) {
          const firstError = jsonError.errors[0];
          if (firstError?.detail) {
            errorMessage = firstError.detail;
          }
        } else if (jsonError.errors?.detail) {
          errorMessage = jsonError.errors.detail;
        } else if (typeof jsonError.errors === "string") {
          errorMessage = jsonError.errors;
        } else {
          errorMessage = JSON.stringify(jsonError.errors);
        }
      }
    } catch (parseError) {
      console.error("Failed to parse JSON error blob:", parseError);
    }
  } else {
    if (errorResponse?.data?.errors) {
      const errorData = errorResponse.data.errors;
      if (Array.isArray(errorData)) {
        const firstError = errorData[0];
        if (firstError?.detail) {
          errorMessage = firstError.detail;
        }
      } else if (errorData?.detail) {
        errorMessage = errorData.detail;
      } else if (typeof errorData === "string") {
        errorMessage = errorData;
      } else {
        errorMessage = JSON.stringify(errorData);
      }
    } else if (errorResponse?.data?.message) {
      errorMessage = errorResponse.data.message;
    }
  }

  return errorMessage;
}
