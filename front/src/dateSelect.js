export default function dateSelect(dates, timeframe = "1D")
{
    // Contains the delta in days and a trunc function specific for this timeframe
    const timeframes = {
        // Return dates hourly
        "1D": {delta: 1, truncFn: (date) => { return new Date(date).setMinutes(0, 0, 0); }},
        // Return dates every 12 hours
        "1M": {delta: 31, truncFn: (date) => { return new Date(date).setHours((date.getHours() >= 12) ? 12 : 0, 0, 0, 0); }},
        // Return dates every day
        "3M": {delta: 90, truncFn: (date) => { return new Date(date).setHours(0, 0, 0, 0); }},
        // Return dates every first day of the month
        "1Y": {delta: 365, truncFn: (date) => { let d = new Date(date); d.setDate(1); d.setHours(0, 0, 0, 0); return d; }},
    }

    const {delta, truncFn} = timeframes[timeframe];
    const today = dates[0]
    // Calculate the start date using delta
    const start = new Date(today - (delta * 24 * 60 * 60 * 1000));

    let outDates = [];
    let truncDates = {};

    for(let date of dates)
    {
        if (date > today) continue;
        if (date < start) break;

        let truncatedDate = truncFn(date);

        if (truncatedDate in truncDates)
        {
            // Since the og array is sorted we can just remove the last inserted element
            outDates.pop();
        }

        outDates.push(new Date(date));
        truncDates[truncatedDate] = date;
    }

    return outDates;
}