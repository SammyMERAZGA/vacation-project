<?php
// Exemple : php scheduling.php thibaud 2025-12-01
class Schedule
{
    public string $team;
    public string $developer;
    public string $date;


    public function __construct(string $team, string $developer, string $date)
    {
        $this->team = $team;
        $this->developer = $developer;
        $this->date = $date;
    }

    public function __toString(): string
    {
        return "Schedule(team='{$this->team}', developer='{$this->developer}', date='{$this->date}')";
    }
}

class Scheduling
{
    private const MIN_LEADS_PRESENT = 1;
    private const MIN_DEVELOPERS_PRESENT = 2;

    private array $TEAMS = [
        'squad_1' => [
            'lead_devs' => ['alexandre', 'wahid'],
            'developers' => ['thierry', 'xavier', 'guillaume', 'nesrine']
        ],
        'squad_2' => [
            'lead_devs' => ['thibaud', 'jean-christophe'],
            'developers' => ['olivier', 'thierry', 'mustapha', 'lucile']
        ]
    ];

    private array $SCHEDULE = [];

    public function add(string $developer, string $startDate, ?string $endDate = null): bool
    {
        $team = $this->findTeamByDeveloper($developer);

        if (!$team) {
            throw new Exception('Team not found');
        }

        $dates = $this->getDates($startDate, $endDate ?? $startDate);

        foreach ($dates as $date)
        {
            if(!$this->canBeAbsent($team, $developer, $date))
            {
                echo "Schedule refused";
                return false;
            }
        }

        //$schedule = new Schedule($team, $developer, $date);
        //$this->SCHEDULE[] = $schedule;
        foreach ($dates as $date)
        {
            $this->SCHEDULE[]= new Schedule($team, $developer, $date);
        }
        echo "Schedule added\n";
        return true;
    }

    public function list(): void
    {
        foreach ($this->SCHEDULE as $schedule) {
            echo $schedule . "\n";
        }
    }

    private function findTeamByDeveloper(string $developer): ?string
    {
        foreach ($this->TEAMS as $team => $members) {
            if (in_array($developer, $members['lead_devs']) || in_array($developer, $members['developers'])) {
                return $team;
            }
        }
        return null;
    }

    private function getDates(string $startDate, string $endDate): array 
    {
        $start = DateTime::createFromFormat('!Y-m-d', $startDate);
        $end = DateTime::createFromFormat('!Y-m-d', $endDate);

        if (!$start || !$end || $end < $start)
            {
                throw new Exception('Invalid dates');
            }

        $dates= [];
        while ($start <= $end) 
        {
            $dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
        return $dates;
    }

    private function canBeAbsent(string $team, string $developer, string $date): bool
    {
        $absents = [$developer];
        foreach ($this->SCHEDULE as $schedule)
        {
            if ($schedule->team === $team && $schedule->date === $date)
            {
                $absents[] = $schedule->developer;
            }
        } 

        $leadsPresent = count(array_diff($this->TEAMS[$team]['lead_devs'], $absents));
        $developersPresent = count(array_diff($this->TEAMS[$team]['developers'], $absents));

        return $leadsPresent >= self::MIN_LEADS_PRESENT && $developersPresent >= self::MIN_DEVELOPERS_PRESENT;
    }

}

if (count($argv) < 3 || count($argv) > 4) {
    fwrite(STDERR, "Developer and date are required\n");
    exit(1);
}

$developer = $argv[1];
$date = $argv[2];

$scheduling = new Scheduling();
$scheduling->add($developer, $date, $argv[3] ?? null);
$scheduling->list();

// php scheduling.php thibaud 2025-12-01 2025-12-03