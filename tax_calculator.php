<?php
/**
 * @author Jayanka Ghosh <jayankaghosh@gmail.com>
 * Income tax calculator (India) as per New tax slabs 2023
 */

if (php_sapi_name() !== "cli") {
    die("This file can only be executed in CLI mode\n");
}

class IncomeTaxCalculator
{

    const REGIME_OLD = 0;
    const REGIME_NEW = 1;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param array $arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    protected function getCliInput(int $argumentNumber, string $prompt = ''): string
    {
        while (!isset($this->arguments[$argumentNumber + 1])) {
            $this->arguments[$argumentNumber + 1] = readline($prompt);
        }
        return $this->arguments[$argumentNumber + 1];
    }

    public function calculate(): float
    {
        $income = floatval($this->getCliInput(0, 'Income: '));
        $regime = strtolower($this->getCliInput(1, 'Regime (old/new): '));
        if ($regime === 'old') {
            $regime = static::REGIME_OLD;
        } else {
            $regime = static::REGIME_NEW;
        }

        $totalTax = 0;
        $slabs = $this->getTaxSlabs($regime);
        $incomeLeft = $income;
        $allocatedSlabs = [];

        foreach ($slabs as $taxPercent => $slab) {
            $amount = $incomeLeft - $slab;
            if ($amount > 0) {
                $allocatedSlabs[] = [
                    'amount' => $amount,
                    'rate' => $taxPercent
                ];
                $incomeLeft -= $amount;
            }
        }

        foreach ($allocatedSlabs as $allocatedSlab) {
            $totalTax += ($allocatedSlab['amount'] * $allocatedSlab['rate'] / 100);
        }
        return $totalTax;
    }

    /**
     * @param int $regime
     * @return int[]
     */
    protected function getTaxSlabs(int $regime): array
    {
        if ($regime === static::REGIME_OLD) {
            return [
                30 => 1500000,
                20 => 1200000,
                15 => 900000,
                10 => 600000,
                5 => 300000,
                0 => 0
            ];
        } else { // If not old regime, assume new regime
            return [
                30 => 1500000,
                20 => 1200000,
                15 => 900000,
                10 => 700000,
                0 => 0
            ];
        }
    }
}

$incomeTaxCalculator = new IncomeTaxCalculator($argv);
$tax = $incomeTaxCalculator->calculate();
echo "Your total income tax is $tax\n";
