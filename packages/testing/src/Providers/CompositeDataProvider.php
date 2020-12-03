<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use function array_merge;
use function array_reduce;
use function array_reverse;
use function array_slice;
use function reset;

/**
 * The data provider allows merging several data providers into one. It works in
 * the following way:
 *
 *      Initial:
 *      [
 *          ['expected a', 'value a'],
 *          ['expected final', 'value final'],
 *      ]
 *      [
 *          ['expected b', 'value b'],
 *          ['expected c', 'value c'],
 *      ]
 *      [
 *          ['expected d', 'value d'],
 *          ['expected e', 'value e'],
 *      ]
 *
 *      Merged:
 *      [
 *          '0 / 0 / 0' => ['expected d', 'value a', 'value b', 'value d'],
 *          '0 / 0 / 1' => ['expected e', 'value a', 'value b', 'value e'],
 *          '0 / 1 / 0' => ['expected d', 'value a', 'value c', 'value d'],
 *          '0 / 1 / 1' => ['expected e', 'value a', 'value c', 'value e'],
 *          '1'         => ['expected final', 'value final'],
 *      ]
 */
class CompositeDataProvider implements DataProvider {
    /**
     * @var DataProvider[]
     */
    private array $providers;

    public function __construct(DataProvider ...$providers) {
        $this->providers = $providers;
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    protected function getProviders(): array {
        return $this->providers;
    }
    // </editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function getData(): array {
        return array_reduce(array_reverse($this->getProviders()), function (?array $previous, DataProvider $current): array {
            // First Iteration?
            if (!$previous) {
                return $current->getData();
            }

            // Merge
            $data = [];

            foreach ($current->getData() as $cKey => $cData) {
                $cExpected   = reset($cData);
                $cParameters = array_slice($cData, 1);

                if ($this->isExpectedFinal($cExpected)) {
                    $data[$cKey] = $cData;
                } else {
                    foreach ($previous as $pKey => $pData) {
                        $key         = "{$cKey} / {$pKey}";
                        $pExpected   = reset($pData);
                        $pParameters = array_slice($pData, 1);
                        $data[$key]  = array_merge([$pExpected], $cParameters, $pParameters);
                    }
                }
            }

            return $data;
        }, null);
    }

    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function isExpectedFinal($expected): bool {
        return $expected instanceof CompositeExpectedInterface
            && $expected->isExpectedFinal();
    }
    // </editor-fold>
}
