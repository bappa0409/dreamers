<script>
    window.AppConfig = Object.freeze({
        dateFormat: @json(setting('date_format', 'd-m-Y')),
        timeFormat: @json(setting('time_format', '12')),
        financialYearStart: @json(setting('financial_year_start', '01-07')),
    });
</script>