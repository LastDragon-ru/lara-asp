<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

/**
 * The data provider allows merging several data providers into one. It works in
 * the following way:
 *
 *      Initial:
 *      [
 *          'a' => ['expected a', 'value a'],
 *          'b' => ['expected b', 'value b'],
 *      ]
 *      [
 *          'a' => ['expected b', 'value b'],
 *          'b' => ['expected c', 'value c'],
 *      ]
 *
 *      Merged:
 *      [
 *          '0 / a' => ['expected a', 'value a'],
 *          '0 / b' => ['expected b', 'value b'],
 *          '1 / a' => ['expected b', 'value b'],
 *          '1 / b' => ['expected c', 'value c'],
 *      ]
 */
class MergeDataProvider extends BaseDataProvider {
    /**
     * @var array<DataProvider>
     */
    private array $providers;

    /**
     * @param array<DataProvider> $providers
     */
    public function __construct(array $providers) {
        $this->providers = $providers;
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    /**
     * @return array<DataProvider>
     */
    protected function getProviders(): array {
        return $this->providers;
    }
    // </editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function getData(bool $raw = false): array {
        $data = [];

        foreach ($this->getProviders() as $name => $provider) {
            foreach ($provider->getData(true) as $k => $v) {
                $data["{$name} / {$k}"] = $v;
            }
        }

        return $this->replaceExpectedValues($data, $raw);
    }
    // </editor-fold>
}
