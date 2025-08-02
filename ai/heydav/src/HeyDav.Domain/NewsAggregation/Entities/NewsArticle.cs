using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.NewsAggregation.Entities;

public class NewsArticle : BaseEntity
{
    private readonly List<string> _tags = new();

    public Guid FeedId { get; private set; }
    public string Title { get; private set; }
    public string? Summary { get; private set; }
    public string? Content { get; private set; }
    public string Url { get; private set; }
    public string? Author { get; private set; }
    public DateTime PublishedDate { get; private set; }
    public DateTime FetchedDate { get; private set; }
    public string? ImageUrl { get; private set; }
    public double RelevanceScore { get; private set; } // 0-1 score from AI
    public bool IsRead { get; private set; }
    public bool IsSaved { get; private set; }
    public DateTime? ReadDate { get; private set; }
    public IReadOnlyList<string> Tags => _tags.AsReadOnly();

    private NewsArticle(
        Guid feedId,
        string title,
        string url,
        DateTime publishedDate,
        string? summary = null,
        string? content = null)
    {
        FeedId = feedId;
        Title = title;
        Url = url;
        PublishedDate = publishedDate;
        Summary = summary;
        Content = content;
        FetchedDate = DateTime.UtcNow;
        RelevanceScore = 0;
        IsRead = false;
        IsSaved = false;
    }

    public static NewsArticle Create(
        Guid feedId,
        string title,
        string url,
        DateTime publishedDate,
        string? summary = null,
        string? content = null,
        string? author = null,
        string? imageUrl = null)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Article title cannot be empty", nameof(title));

        if (string.IsNullOrWhiteSpace(url))
            throw new ArgumentException("Article URL cannot be empty", nameof(url));

        var article = new NewsArticle(feedId, title, url, publishedDate, summary, content)
        {
            Author = author,
            ImageUrl = imageUrl
        };

        return article;
    }

    public void SetRelevanceScore(double score)
    {
        if (score < 0 || score > 1)
            throw new ArgumentOutOfRangeException(nameof(score), "Relevance score must be between 0 and 1");

        RelevanceScore = score;
        UpdateTimestamp();
    }

    public void MarkAsRead()
    {
        if (!IsRead)
        {
            IsRead = true;
            ReadDate = DateTime.UtcNow;
            UpdateTimestamp();
        }
    }

    public void MarkAsUnread()
    {
        IsRead = false;
        ReadDate = null;
        UpdateTimestamp();
    }

    public void Save()
    {
        IsSaved = true;
        UpdateTimestamp();
    }

    public void Unsave()
    {
        IsSaved = false;
        UpdateTimestamp();
    }

    public void AddTag(string tag)
    {
        if (!string.IsNullOrWhiteSpace(tag) && !_tags.Contains(tag, StringComparer.OrdinalIgnoreCase))
        {
            _tags.Add(tag.ToLower());
            UpdateTimestamp();
        }
    }

    public void RemoveTag(string tag)
    {
        if (_tags.Remove(tag.ToLower()))
        {
            UpdateTimestamp();
        }
    }

    public void UpdateContent(string? summary, string? content)
    {
        Summary = summary;
        Content = content;
        UpdateTimestamp();
    }
}