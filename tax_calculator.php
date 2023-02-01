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
    const NEW_REGIME_TAX_THRESHOLD = 700000;
    const OLD_REGIME_TAX_THRESHOLD = 500000;
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

    public function calculate(): array
    {
        $income = floatval($this->getCliInput(0, 'Income: '));
        $regime = strtolower($this->getCliInput(1, 'Regime (old/new): '));
        if ($regime === 'old') {
            $regime = static::REGIME_OLD;
        } else {
            $regime = static::REGIME_NEW;
        }

        $totalTax = 0;
        $slabs = $this->getTaxSlabs($income, $regime);
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
        return [
            'slabs' => array_reverse($allocatedSlabs),
            'amount' => $totalTax
        ];
    }

    /**
     * @param int $regime
     * @return int[]
     */
    protected function getTaxSlabs(float $income, int $regime): array
    {

        if ($regime !== static::REGIME_OLD && $income <= static::NEW_REGIME_TAX_THRESHOLD) {
            return [
                0 => static::NEW_REGIME_TAX_THRESHOLD
            ];
        }

        if ($regime === static::REGIME_OLD && $income <= static::OLD_REGIME_TAX_THRESHOLD) {
            return [
                0 => static::OLD_REGIME_TAX_THRESHOLD
            ];
        }

        if ($regime === static::REGIME_OLD) {
            return [
                30 => 1000000,
                20 => 500000,
                5 => 250000,
                0 => 0
            ];
        } else { // If not old regime, assume new regime
            return [
                30 => 1500000,
                20 => 1200000,
                15 => 900000,
                10 => 600000,
                5 => 300000,
                0 => 0
            ];
        }
    }
}

$incomeTaxCalculator = new IncomeTaxCalculator($argv);
$tax = $incomeTaxCalculator->calculate();
echo sprintf("Your total income tax is %s\n", $tax['amount']);
