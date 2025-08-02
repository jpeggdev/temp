using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.ToolIntegrations.ValueObjects;

public class EncryptedCredentials : ValueObject
{
    public string? EncryptedApiKey { get; private set; }
    public string? EncryptedClientId { get; private set; }
    public string? EncryptedClientSecret { get; private set; }
    public string? EncryptedAccessToken { get; private set; }
    public string? EncryptedRefreshToken { get; private set; }
    public string? EncryptedUsername { get; private set; }
    public string? EncryptedPassword { get; private set; }
    public Dictionary<string, string> EncryptedCustomFields { get; private set; }
    public DateTime? TokenExpiresAt { get; private set; }
    public List<string> Scopes { get; private set; }

    public EncryptedCredentials()
    {
        EncryptedCustomFields = new Dictionary<string, string>();
        Scopes = new List<string>();
    }

    public EncryptedCredentials(
        string? encryptedApiKey = null,
        string? encryptedClientId = null,
        string? encryptedClientSecret = null,
        string? encryptedAccessToken = null,
        string? encryptedRefreshToken = null,
        string? encryptedUsername = null,
        string? encryptedPassword = null,
        Dictionary<string, string>? encryptedCustomFields = null,
        DateTime? tokenExpiresAt = null,
        List<string>? scopes = null)
    {
        EncryptedApiKey = encryptedApiKey;
        EncryptedClientId = encryptedClientId;
        EncryptedClientSecret = encryptedClientSecret;
        EncryptedAccessToken = encryptedAccessToken;
        EncryptedRefreshToken = encryptedRefreshToken;
        EncryptedUsername = encryptedUsername;
        EncryptedPassword = encryptedPassword;
        EncryptedCustomFields = encryptedCustomFields ?? new Dictionary<string, string>();
        TokenExpiresAt = tokenExpiresAt;
        Scopes = scopes ?? new List<string>();
    }

    public bool HasApiKey => !string.IsNullOrEmpty(EncryptedApiKey);
    public bool HasOAuthCredentials => !string.IsNullOrEmpty(EncryptedClientId) && !string.IsNullOrEmpty(EncryptedClientSecret);
    public bool HasAccessToken => !string.IsNullOrEmpty(EncryptedAccessToken);
    public bool HasBasicAuth => !string.IsNullOrEmpty(EncryptedUsername) && !string.IsNullOrEmpty(EncryptedPassword);
    public bool HasRefreshToken => !string.IsNullOrEmpty(EncryptedRefreshToken);

    public bool IsTokenExpired => TokenExpiresAt.HasValue && TokenExpiresAt.Value <= DateTime.UtcNow;
    public bool IsTokenExpiringSoon => TokenExpiresAt.HasValue && TokenExpiresAt.Value <= DateTime.UtcNow.AddMinutes(10);

    public EncryptedCredentials WithApiKey(string encryptedApiKey)
    {
        return new EncryptedCredentials(
            encryptedApiKey,
            EncryptedClientId,
            EncryptedClientSecret,
            EncryptedAccessToken,
            EncryptedRefreshToken,
            EncryptedUsername,
            EncryptedPassword,
            EncryptedCustomFields,
            TokenExpiresAt,
            Scopes);
    }

    public EncryptedCredentials WithOAuthCredentials(string encryptedClientId, string encryptedClientSecret)
    {
        return new EncryptedCredentials(
            EncryptedApiKey,
            encryptedClientId,
            encryptedClientSecret,
            EncryptedAccessToken,
            EncryptedRefreshToken,
            EncryptedUsername,
            EncryptedPassword,
            EncryptedCustomFields,
            TokenExpiresAt,
            Scopes);
    }

    public EncryptedCredentials WithAccessToken(string encryptedAccessToken, DateTime? expiresAt = null)
    {
        return new EncryptedCredentials(
            EncryptedApiKey,
            EncryptedClientId,
            EncryptedClientSecret,
            encryptedAccessToken,
            EncryptedRefreshToken,
            EncryptedUsername,
            EncryptedPassword,
            EncryptedCustomFields,
            expiresAt,
            Scopes);
    }

    public EncryptedCredentials WithRefreshToken(string encryptedRefreshToken)
    {
        return new EncryptedCredentials(
            EncryptedApiKey,
            EncryptedClientId,
            EncryptedClientSecret,
            EncryptedAccessToken,
            encryptedRefreshToken,
            EncryptedUsername,
            EncryptedPassword,
            EncryptedCustomFields,
            TokenExpiresAt,
            Scopes);
    }

    public EncryptedCredentials WithBasicAuth(string encryptedUsername, string encryptedPassword)
    {
        return new EncryptedCredentials(
            EncryptedApiKey,
            EncryptedClientId,
            EncryptedClientSecret,
            EncryptedAccessToken,
            EncryptedRefreshToken,
            encryptedUsername,
            encryptedPassword,
            EncryptedCustomFields,
            TokenExpiresAt,
            Scopes);
    }

    public EncryptedCredentials WithScopes(List<string> scopes)
    {
        return new EncryptedCredentials(
            EncryptedApiKey,
            EncryptedClientId,
            EncryptedClientSecret,
            EncryptedAccessToken,
            EncryptedRefreshToken,
            EncryptedUsername,
            EncryptedPassword,
            EncryptedCustomFields,
            TokenExpiresAt,
            scopes);
    }

    public EncryptedCredentials WithCustomField(string key, string encryptedValue)
    {
        var newCustomFields = new Dictionary<string, string>(EncryptedCustomFields)
        {
            [key] = encryptedValue
        };

        return new EncryptedCredentials(
            EncryptedApiKey,
            EncryptedClientId,
            EncryptedClientSecret,
            EncryptedAccessToken,
            EncryptedRefreshToken,
            EncryptedUsername,
            EncryptedPassword,
            newCustomFields,
            TokenExpiresAt,
            Scopes);
    }

    public string? GetCustomField(string key)
    {
        return EncryptedCustomFields.TryGetValue(key, out var value) ? value : null;
    }

    public bool HasScope(string scope)
    {
        return Scopes.Contains(scope, StringComparer.OrdinalIgnoreCase);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return EncryptedApiKey ?? string.Empty;
        yield return EncryptedClientId ?? string.Empty;
        yield return EncryptedClientSecret ?? string.Empty;
        yield return EncryptedAccessToken ?? string.Empty;
        yield return EncryptedRefreshToken ?? string.Empty;
        yield return EncryptedUsername ?? string.Empty;
        yield return EncryptedPassword ?? string.Empty;
        yield return EncryptedCustomFields;
        yield return TokenExpiresAt ?? DateTime.MinValue;
        yield return Scopes;
    }
}