using Microsoft.Extensions.Logging;
using System.Text.Json;
using System.Net.Http.Headers;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Application.ToolIntegrations.Interfaces;
using HeyDav.Application.ToolIntegrations.Models;
using HeyDav.Infrastructure.ExternalTools.Common;

namespace HeyDav.Infrastructure.ExternalTools.GitHub;

public class GitHubIntegration : BaseToolIntegration, IToolIntegration
{
    private readonly HttpClient _httpClient;
    private readonly ILogger<GitHubIntegration> _logger;

    public override string ToolName => "GitHub";
    public override ToolType ToolType => ToolType.VersionControl;
    public override string BaseUrl => "https://api.github.com";

    public GitHubIntegration(HttpClient httpClient, ILogger<GitHubIntegration> logger)
        : base(logger)
    {
        _httpClient = httpClient;
        _logger = logger;
        
        // Configure HTTP client
        _httpClient.BaseAddress = new Uri(BaseUrl);
        _httpClient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/vnd.github+json"));
        _httpClient.DefaultRequestHeaders.UserAgent.Add(new ProductInfoHeaderValue("HeyDav", "1.0"));
    }

    public override async Task<AuthenticationResult> AuthenticateAsync(ToolConnection connection, CancellationToken cancellationToken = default)
    {
        try
        {
            _logger.LogInformation("Authenticating GitHub connection: {ConnectionId}", connection.Id);

            // GitHub uses Personal Access Tokens or OAuth
            var accessToken = await GetDecryptedCredentialAsync(connection.Credentials.EncryptedAccessToken);
            if (string.IsNullOrEmpty(accessToken))
            {
                return new AuthenticationResult(false, ErrorMessage: "No access token provided");
            }

            // Test the token by making a request to the user endpoint
            using var request = new HttpRequestMessage(HttpMethod.Get, "/user");
            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

            using var response = await _httpClient.SendAsync(request, cancellationToken);
            
            if (response.IsSuccessStatusCode)
            {
                var userInfo = await response.Content.ReadAsStringAsync(cancellationToken);
                var userJson = JsonDocument.Parse(userInfo);
                var username = userJson.RootElement.GetProperty("login").GetString();

                _logger.LogInformation("Successfully authenticated GitHub user: {Username}", username);

                return new AuthenticationResult(
                    IsSuccessful: true,
                    AccessToken: accessToken,
                    Scopes: ExtractScopesFromResponse(response));
            }
            else
            {
                var errorContent = await response.Content.ReadAsStringAsync(cancellationToken);
                _logger.LogWarning("GitHub authentication failed: {StatusCode} - {Error}", response.StatusCode, errorContent);
                
                return new AuthenticationResult(false, ErrorMessage: $"Authentication failed: {response.StatusCode}");
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error during GitHub authentication for connection {ConnectionId}", connection.Id);
            return new AuthenticationResult(false, ErrorMessage: ex.Message);
        }
    }

    public override async Task<ToolCapabilityDiscovery> DiscoverCapabilitiesAsync(ToolConnection connection, CancellationToken cancellationToken = default)
    {
        var capabilities = new List<DiscoveredCapability>
        {
            new("list_repositories", "List user repositories", CapabilityType.Read, true, new List<string> { "repo" }),
            new("get_repository", "Get repository details", CapabilityType.Read, true, new List<string> { "repo" }, 
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string" }),
            new("create_repository", "Create a new repository", CapabilityType.Create, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["name"] = "string", ["description"] = "string", ["private"] = "boolean" }),
            new("list_issues", "List repository issues", CapabilityType.Read, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["state"] = "string" }),
            new("create_issue", "Create a new issue", CapabilityType.Create, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["title"] = "string", ["body"] = "string" }),
            new("update_issue", "Update an existing issue", CapabilityType.Update, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["issue_number"] = "integer", ["title"] = "string", ["body"] = "string", ["state"] = "string" }),
            new("list_pull_requests", "List repository pull requests", CapabilityType.Read, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["state"] = "string" }),
            new("create_pull_request", "Create a new pull request", CapabilityType.Create, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["title"] = "string", ["body"] = "string", ["head"] = "string", ["base"] = "string" }),
            new("list_commits", "List repository commits", CapabilityType.Read, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["sha"] = "string", ["path"] = "string" }),
            new("get_file_content", "Get file content from repository", CapabilityType.Read, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["path"] = "string", ["ref"] = "string" }),
            new("create_webhook", "Create repository webhook", CapabilityType.Create, true, new List<string> { "repo", "admin:repo_hook" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string", ["url"] = "string", ["events"] = "array" }),
            new("list_branches", "List repository branches", CapabilityType.Read, true, new List<string> { "repo" },
                new Dictionary<string, object> { ["owner"] = "string", ["repo"] = "string" }),
            new("get_user", "Get authenticated user information", CapabilityType.Read, true),
            new("list_organizations", "List user organizations", CapabilityType.Read, true, new List<string> { "read:org" })
        };

        return new ToolCapabilityDiscovery(
            ConnectionId: connection.Id,
            Capabilities: capabilities,
            Metadata: new Dictionary<string, object>
            {
                ["api_version"] = "2022-11-28",
                ["rate_limit"] = "5000 requests/hour",
                ["webhook_events"] = new[]
                {
                    "push", "pull_request", "issues", "issue_comment", "pull_request_review",
                    "pull_request_review_comment", "commit_comment", "create", "delete",
                    "deployment", "deployment_status", "fork", "gollum", "member",
                    "membership", "milestone", "organization", "page_build", "project_card",
                    "project_column", "project", "public", "release", "repository", "status",
                    "team", "team_add", "watch"
                }
            });
    }

    public override async Task<CapabilityResult> ExecuteCapabilityAsync(ToolConnection connection, string capabilityName, object? parameters = null, CancellationToken cancellationToken = default)
    {
        var startTime = DateTime.UtcNow;

        try
        {
            var accessToken = await GetDecryptedCredentialAsync(connection.Credentials.EncryptedAccessToken);
            if (string.IsNullOrEmpty(accessToken))
            {
                return new CapabilityResult(false, ErrorMessage: "No access token available");
            }

            var result = capabilityName switch
            {
                "list_repositories" => await ListRepositoriesAsync(accessToken, parameters, cancellationToken),
                "get_repository" => await GetRepositoryAsync(accessToken, parameters, cancellationToken),
                "create_repository" => await CreateRepositoryAsync(accessToken, parameters, cancellationToken),
                "list_issues" => await ListIssuesAsync(accessToken, parameters, cancellationToken),
                "create_issue" => await CreateIssueAsync(accessToken, parameters, cancellationToken),
                "update_issue" => await UpdateIssueAsync(accessToken, parameters, cancellationToken),
                "list_pull_requests" => await ListPullRequestsAsync(accessToken, parameters, cancellationToken),
                "create_pull_request" => await CreatePullRequestAsync(accessToken, parameters, cancellationToken),
                "list_commits" => await ListCommitsAsync(accessToken, parameters, cancellationToken),
                "get_file_content" => await GetFileContentAsync(accessToken, parameters, cancellationToken),
                "create_webhook" => await CreateWebhookAsync(accessToken, parameters, cancellationToken),
                "list_branches" => await ListBranchesAsync(accessToken, parameters, cancellationToken),
                "get_user" => await GetUserAsync(accessToken, cancellationToken),
                "list_organizations" => await ListOrganizationsAsync(accessToken, cancellationToken),
                _ => throw new CapabilityNotSupportedException(capabilityName, $"Capability '{capabilityName}' is not supported by GitHub integration")
            };

            var executionTime = DateTime.UtcNow - startTime;
            return new CapabilityResult(true, result, ExecutionTime: executionTime);
        }
        catch (Exception ex)
        {
            var executionTime = DateTime.UtcNow - startTime;
            _logger.LogError(ex, "Error executing GitHub capability {Capability} for connection {ConnectionId}", capabilityName, connection.Id);
            return new CapabilityResult(false, ErrorMessage: ex.Message, ExecutionTime: executionTime);
        }
    }

    public override async Task<bool> TestConnectionAsync(ToolConnection connection, CancellationToken cancellationToken = default)
    {
        try
        {
            var authResult = await AuthenticateAsync(connection, cancellationToken);
            return authResult.IsSuccessful;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Connection test failed for GitHub connection {ConnectionId}", connection.Id);
            return false;
        }
    }

    // GitHub-specific capability implementations
    private async Task<object> ListRepositoriesAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(HttpMethod.Get, "/user/repos?sort=updated&direction=desc");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> GetRepositoryAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");

        using var request = new HttpRequestMessage(HttpMethod.Get, $"/repos/{owner}/{repo}");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> CreateRepositoryAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var name = paramDict.GetValueOrDefault("name")?.ToString() ?? throw new ArgumentException("name parameter is required");
        var description = paramDict.GetValueOrDefault("description")?.ToString();
        var isPrivate = bool.Parse(paramDict.GetValueOrDefault("private")?.ToString() ?? "false");

        var requestBody = new
        {
            name,
            description,
            @private = isPrivate,
            auto_init = true
        };

        using var request = new HttpRequestMessage(HttpMethod.Post, "/user/repos");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
        request.Content = new StringContent(JsonSerializer.Serialize(requestBody), System.Text.Encoding.UTF8, "application/json");

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> ListIssuesAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var state = paramDict.GetValueOrDefault("state")?.ToString() ?? "open";

        using var request = new HttpRequestMessage(HttpMethod.Get, $"/repos/{owner}/{repo}/issues?state={state}");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> CreateIssueAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var title = paramDict.GetValueOrDefault("title")?.ToString() ?? throw new ArgumentException("title parameter is required");
        var body = paramDict.GetValueOrDefault("body")?.ToString();

        var requestBody = new
        {
            title,
            body
        };

        using var request = new HttpRequestMessage(HttpMethod.Post, $"/repos/{owner}/{repo}/issues");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
        request.Content = new StringContent(JsonSerializer.Serialize(requestBody), System.Text.Encoding.UTF8, "application/json");

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> UpdateIssueAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var issueNumber = paramDict.GetValueOrDefault("issue_number")?.ToString() ?? throw new ArgumentException("issue_number parameter is required");
        var title = paramDict.GetValueOrDefault("title")?.ToString();
        var body = paramDict.GetValueOrDefault("body")?.ToString();
        var state = paramDict.GetValueOrDefault("state")?.ToString();

        var requestBody = new Dictionary<string, object>();
        if (!string.IsNullOrEmpty(title)) requestBody["title"] = title;
        if (!string.IsNullOrEmpty(body)) requestBody["body"] = body;
        if (!string.IsNullOrEmpty(state)) requestBody["state"] = state;

        using var request = new HttpRequestMessage(HttpMethod.Patch, $"/repos/{owner}/{repo}/issues/{issueNumber}");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
        request.Content = new StringContent(JsonSerializer.Serialize(requestBody), System.Text.Encoding.UTF8, "application/json");

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> ListPullRequestsAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var state = paramDict.GetValueOrDefault("state")?.ToString() ?? "open";

        using var request = new HttpRequestMessage(HttpMethod.Get, $"/repos/{owner}/{repo}/pulls?state={state}");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> CreatePullRequestAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var title = paramDict.GetValueOrDefault("title")?.ToString() ?? throw new ArgumentException("title parameter is required");
        var body = paramDict.GetValueOrDefault("body")?.ToString();
        var head = paramDict.GetValueOrDefault("head")?.ToString() ?? throw new ArgumentException("head parameter is required");
        var baseRef = paramDict.GetValueOrDefault("base")?.ToString() ?? throw new ArgumentException("base parameter is required");

        var requestBody = new
        {
            title,
            body,
            head,
            @base = baseRef
        };

        using var request = new HttpRequestMessage(HttpMethod.Post, $"/repos/{owner}/{repo}/pulls");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
        request.Content = new StringContent(JsonSerializer.Serialize(requestBody), System.Text.Encoding.UTF8, "application/json");

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> ListCommitsAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var sha = paramDict.GetValueOrDefault("sha")?.ToString();
        var path = paramDict.GetValueOrDefault("path")?.ToString();

        var url = $"/repos/{owner}/{repo}/commits";
        var queryParams = new List<string>();
        if (!string.IsNullOrEmpty(sha)) queryParams.Add($"sha={sha}");
        if (!string.IsNullOrEmpty(path)) queryParams.Add($"path={path}");
        
        if (queryParams.Count > 0)
        {
            url += "?" + string.Join("&", queryParams);
        }

        using var request = new HttpRequestMessage(HttpMethod.Get, url);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> GetFileContentAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var path = paramDict.GetValueOrDefault("path")?.ToString() ?? throw new ArgumentException("path parameter is required");
        var refParam = paramDict.GetValueOrDefault("ref")?.ToString();

        var url = $"/repos/{owner}/{repo}/contents/{path}";
        if (!string.IsNullOrEmpty(refParam))
        {
            url += $"?ref={refParam}";
        }

        using var request = new HttpRequestMessage(HttpMethod.Get, url);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> CreateWebhookAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");
        var url = paramDict.GetValueOrDefault("url")?.ToString() ?? throw new ArgumentException("url parameter is required");
        var events = paramDict.GetValueOrDefault("events") as IEnumerable<object> ?? new[] { "push" };

        var requestBody = new
        {
            name = "web",
            active = true,
            events = events.Select(e => e.ToString()).ToArray(),
            config = new
            {
                url,
                content_type = "json",
                insecure_ssl = "0"
            }
        };

        using var request = new HttpRequestMessage(HttpMethod.Post, $"/repos/{owner}/{repo}/hooks");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
        request.Content = new StringContent(JsonSerializer.Serialize(requestBody), System.Text.Encoding.UTF8, "application/json");

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> ListBranchesAsync(string accessToken, object? parameters, CancellationToken cancellationToken)
    {
        var paramDict = ExtractParameters(parameters);
        var owner = paramDict.GetValueOrDefault("owner")?.ToString() ?? throw new ArgumentException("owner parameter is required");
        var repo = paramDict.GetValueOrDefault("repo")?.ToString() ?? throw new ArgumentException("repo parameter is required");

        using var request = new HttpRequestMessage(HttpMethod.Get, $"/repos/{owner}/{repo}/branches");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> GetUserAsync(string accessToken, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(HttpMethod.Get, "/user");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private async Task<object> ListOrganizationsAsync(string accessToken, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(HttpMethod.Get, "/user/orgs");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _httpClient.SendAsync(request, cancellationToken);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<object>(content) ?? new { };
    }

    private static List<string> ExtractScopesFromResponse(HttpResponseMessage response)
    {
        var scopes = new List<string>();
        
        if (response.Headers.TryGetValues("X-OAuth-Scopes", out var scopeValues))
        {
            foreach (var scopeValue in scopeValues)
            {
                scopes.AddRange(scopeValue.Split(',', StringSplitOptions.RemoveEmptyEntries)
                    .Select(s => s.Trim()));
            }
        }

        return scopes;
    }
}