using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.NewsAggregation.Entities;

public class NewsFeed : BaseEntity
{
    private readonly List<string> _keywords = new();
    private readonly List<string> _categories = new();

    public string Name { get; private set; }
    public string Url { get; private set; }
    public FeedType Type { get; private set; }
    public bool IsActive { get; private set; }
    public DateTime? LastFetchedAt { get; private set; }
    public int FetchIntervalMinutes { get; private set; }
    public IReadOnlyList<string> Keywords => _keywords.AsReadOnly();
    public IReadOnlyList<string> Categories => _categories.AsReadOnly();

    private NewsFeed(string name, string url, FeedType type, int fetchIntervalMinutes = 60)
    {
        Name = name;
        Url = url;
        Type = type;
        FetchIntervalMinutes = fetchIntervalMinutes;
        IsActive = true;
    }

    public static NewsFeed Create(string name, string url, FeedType type, int fetchIntervalMinutes = 60)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Feed name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(url))
            throw new ArgumentException("Feed URL cannot be empty", nameof(url));

        if (fetchIntervalMinutes < 5)
            throw new ArgumentException("Fetch interval must be at least 5 minutes", nameof(fetchIntervalMinutes));

        return new NewsFeed(name, url, type, fetchIntervalMinutes);
    }

    public void UpdateDetails(string name, string url, int fetchIntervalMinutes)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Feed name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(url))
            throw new ArgumentException("Feed URL cannot be empty", nameof(url));

        if (fetchIntervalMinutes < 5)
            throw new ArgumentException("Fetch interval must be at least 5 minutes", nameof(fetchIntervalMinutes));

        Name = name;
        Url = url;
        FetchIntervalMinutes = fetchIntervalMinutes;
        UpdateTimestamp();
    }

    public void MarkAsFetched()
    {
        LastFetchedAt = DateTime.UtcNow;
        UpdateTimestamp();
    }

    public bool ShouldFetch()
    {
        if (!IsActive)
            return false;

        if (LastFetchedAt == null)
            return true;

        return DateTime.UtcNow >= LastFetchedAt.Value.AddMinutes(FetchIntervalMinutes);
    }

    public void Activate()
    {
        IsActive = true;
        UpdateTimestamp();
    }

    public void Deactivate()
    {
        IsActive = false;
        UpdateTimestamp();
    }

    public void AddKeyword(string keyword)
    {
        if (!string.IsNullOrWhiteSpace(keyword) && !_keywords.Contains(keyword, StringComparer.OrdinalIgnoreCase))
        {
            _keywords.Add(keyword.ToLower());
            UpdateTimestamp();
        }
    }

    public void RemoveKeyword(string keyword)
    {
        if (_keywords.Remove(keyword.ToLower()))
        {
            UpdateTimestamp();
        }
    }

    public void AddCategory(string category)
    {
        if (!string.IsNullOrWhiteSpace(category) && !_categories.Contains(category, StringComparer.OrdinalIgnoreCase))
        {
            _categories.Add(category);
            UpdateTimestamp();
        }
    }

    public void RemoveCategory(string category)
    {
        if (_categories.Remove(category))
        {
            UpdateTimestamp();
        }
    }
}

public enum FeedType
{
    RSS,
    Atom,
    API,
    WebScraping
}