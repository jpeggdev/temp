namespace HeyDav.Application.Services;

public interface IExternalAiService
{
    Task<string> ProcessWithClaudeAsync(string query, string context = "");
    Task<string> ProcessWithGeminiAsync(string query, string context = "");
    Task<string> ProcessWithOpenAiAsync(string query, string context = "");
    Task<bool> IsProviderAvailableAsync(ExternalAiProvider provider);
    Task<List<ExternalAiProvider>> GetAvailableProvidersAsync();
}

public enum ExternalAiProvider
{
    Claude,
    Gemini,
    OpenAI,
    Auto
}