using HeyDav.Domain.Common.Base;
using HeyDav.Domain.TodoManagement.Enums;

namespace HeyDav.Domain.TodoManagement.ValueObjects;

public class RecurrencePattern : ValueObject
{
    public RecurrenceType Type { get; }
    public int Interval { get; }
    public DayOfWeek[]? DaysOfWeek { get; }
    public int? DayOfMonth { get; }
    public DateTime? EndDate { get; }
    public int? MaxOccurrences { get; }

    private RecurrencePattern(
        RecurrenceType type,
        int interval = 1,
        DayOfWeek[]? daysOfWeek = null,
        int? dayOfMonth = null,
        DateTime? endDate = null,
        int? maxOccurrences = null)
    {
        Type = type;
        Interval = interval;
        DaysOfWeek = daysOfWeek;
        DayOfMonth = dayOfMonth;
        EndDate = endDate;
        MaxOccurrences = maxOccurrences;
    }

    public static RecurrencePattern None() => new(RecurrenceType.None);
    
    public static RecurrencePattern Daily(int interval = 1) => 
        new(RecurrenceType.Daily, interval);
    
    public static RecurrencePattern Weekly(int interval = 1, params DayOfWeek[] daysOfWeek) => 
        new(RecurrenceType.Weekly, interval, daysOfWeek);
    
    public static RecurrencePattern Monthly(int dayOfMonth, int interval = 1) => 
        new(RecurrenceType.Monthly, interval, dayOfMonth: dayOfMonth);

    public DateTime? GetNextOccurrence(DateTime from)
    {
        if (Type == RecurrenceType.None)
            return null;

        return Type switch
        {
            RecurrenceType.Daily => from.AddDays(Interval),
            RecurrenceType.Weekly => GetNextWeeklyOccurrence(from),
            RecurrenceType.Monthly => GetNextMonthlyOccurrence(from),
            _ => null
        };
    }

    private DateTime GetNextWeeklyOccurrence(DateTime from)
    {
        var nextDate = from.AddDays(1);
        while (true)
        {
            if (DaysOfWeek?.Contains(nextDate.DayOfWeek) == true)
                return nextDate;
            nextDate = nextDate.AddDays(1);
        }
    }

    private DateTime GetNextMonthlyOccurrence(DateTime from)
    {
        var nextMonth = from.AddMonths(Interval);
        var day = Math.Min(DayOfMonth ?? 1, DateTime.DaysInMonth(nextMonth.Year, nextMonth.Month));
        return new DateTime(nextMonth.Year, nextMonth.Month, day);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Type;
        yield return Interval;
        yield return DaysOfWeek?.GetHashCode() ?? 0;
        yield return DayOfMonth ?? 0;
        yield return EndDate ?? DateTime.MaxValue;
        yield return MaxOccurrences ?? 0;
    }
}