using Microsoft.Extensions.Logging;
using HeyDav.Application.Services;

namespace HeyDav.Infrastructure.Services;

public class ExternalAiService : IExternalAiService
{
    private readonly ILogger<ExternalAiService> _logger;

    public ExternalAiService(ILogger<ExternalAiService> logger)
    {
        _logger = logger;
    }

    public async Task<string> ProcessWithClaudeAsync(string query, string context = "")
    {
        _logger.LogInformation("Processing with Claude: {Query}", query);
        
        // Placeholder implementation - in production this would integrate with Claude API or CLI
        await Task.Delay(100); // Simulate processing time
        
        return $"Claude response to: {query}";
    }

    public async Task<string> ProcessWithGeminiAsync(string query, string context = "")
    {
        _logger.LogInformation("Processing with Gemini: {Query}", query);
        
        // Placeholder implementation - in production this would integrate with Gemini API or CLI
        await Task.Delay(100); // Simulate processing time
        
        return $"Gemini response to: {query}";
    }

    public async Task<string> ProcessWithOpenAiAsync(string query, string context = "")
    {
        _logger.LogInformation("Processing with OpenAI: {Query}", query);
        
        // Placeholder implementation - in production this would integrate with OpenAI API or CLI
        await Task.Delay(100); // Simulate processing time
        
        return $"OpenAI response to: {query}";
    }

    public async Task<bool> IsProviderAvailableAsync(ExternalAiProvider provider)
    {
        // Placeholder implementation - in production this would check if the CLI tool is installed
        // or if API keys are configured, etc.
        _logger.LogDebug("Checking availability of provider: {Provider}", provider);
        
        await Task.Delay(10);
        
        // For demo purposes, assume Claude is always available
        return provider == ExternalAiProvider.Claude;
    }

    public async Task<List<ExternalAiProvider>> GetAvailableProvidersAsync()
    {
        _logger.LogDebug("Getting available AI providers");
        
        var availableProviders = new List<ExternalAiProvider>();
        
        // Check each provider
        foreach (var provider in Enum.GetValues<ExternalAiProvider>())
        {
            if (provider == ExternalAiProvider.Auto) continue;
            
            if (await IsProviderAvailableAsync(provider))
            {
                availableProviders.Add(provider);
            }
        }
        
        return availableProviders;
    }
}