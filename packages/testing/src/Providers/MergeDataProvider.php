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
class MergeDataProvider implements DataProvider {
    /**
     * @var array<\LastDragon_ru\LaraASP\Testing\Providers\DataProvider>
     */
    private array $providers;

    /**
     * @param array<\LastDragon_ru\LaraASP\Testing\Providers\DataProvider> $providers
     */
    public function __construct(array $providers) {
        $this->providers = $providers;
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    /**
     * @return array<\LastDragon_ru\LaraASP\Testing\Providers\DataProvider>
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
    public function getData(): array {
        $data = [];

        foreach ($this->getProviders() as $name => $provider) {
            foreach ($provider->getData() as $k => $v) {
                $data["{$name} / {$k}"] = $v;
            }
        }

        return $data;
    }
    // </editor-fold>
}
