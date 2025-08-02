export function extractVimeoId(input: string): string {
  if (/^\d+$/.test(input)) {
    return input;
  }

  if (input.includes("vimeo.com/")) {
    const parts = input.split("vimeo.com/");
    const remainder = parts[1] || "";
    return remainder.split(/[/?#]/)[0];
  }

  return input;
}

export function extractYouTubeId(input: string): string {
  if (/^[a-zA-Z0-9_-]{8,}$/.test(input)) {
    return input;
  }

  if (input.includes("youtu.be/")) {
    const parts = input.split("youtu.be/");
    const remainder = parts[1] || "";
    return remainder.split(/[?&#]/)[0];
  }

  if (input.includes("watch?v=")) {
    const parts = input.split("watch?v=");
    const remainder = parts[1] || "";
    return remainder.split(/[&#]/)[0];
  }

  return input;
}

export function getFileNameFromUrl(url: string): string {
  try {
    const { pathname } = new URL(url);
    const segments = pathname.split("/");
    let filename = segments[segments.length - 1] || "File";
    if (!filename) return "File";

    filename = filename.split(/[?#]/)[0];
    return filename || "File";
  } catch {
    return "File";
  }
}
