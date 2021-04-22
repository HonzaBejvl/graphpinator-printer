<?php

declare(strict_types = 1);

namespace Graphpinator\Printer;

final class HtmlVisitor implements PrintComponentVisitor
{
    public function visitSchema(\Graphpinator\Type\Schema $schema) : string
    {
        $normalizedDescription = static::normalizeString($schema->getQuery()->getDescription());
        $mutation = $schema->getMutation() instanceof \Graphpinator\Type\Type
            ? '<a class="field-type" href="#graphql-type-' . $schema->getMutation()->getName() . '" title="' . static::normalizeString($schema->getMutation()->getDescription()) . '">' . $schema->getMutation()->getName() . '</a>'
            : '<span class="null">null</span>';

        $subscription = $schema->getSubscription() instanceof \Graphpinator\Type\Type
            ? '<a class="field-type" href="#graphql-type-' . $schema->getSubscription()->getName() . '" title="' . static::normalizeString($schema->getSubscription()->getDescription()) . '">' . $schema->getSubscription()->getName() . '</a>'
            : $subscription = '<span class="null">null</span>';

        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($schema->getDescription())}</span>
                <span class="keyword">schema</span>&nbsp;
                <span class="bracket-curly">{</span>
            </div>
            <div class="line offset-1">
                <span class="field-name">query</span>
                <span class="colon">:</span>&nbsp;
                <a class="field-type" href="#graphql-type-Query" title="{$normalizedDescription}">{$schema->getQuery()->getName()}</a>
            </div>
            <div class="line offset-1">
                <span class="field-name">mutation</span>
                <span class="colon">:</span>&nbsp;
                {$mutation}
            </div>
            <div class="line offset-1">
                <span class="field-name">subscription</span>
                <span class="colon">:</span>&nbsp;
                {$subscription}
            </div>
            <div class="line">
                <span class="bracket-curly">}</span>
            </div>
        </section>
        EOL;
    }

    public function visitType(\Graphpinator\Type\Type $type) : string
    {
        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($type->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$type->getName()}">type</span>&nbsp;
                <span class="typename">{$type->getName()}</span>
                <span class="implements">{$this->printImplements($type->getInterfaces())}</span>
                <span class="usage">{$this->printDirectiveUsages($type->getDirectiveUsages())}</span>&nbsp;
                <span class="bracket-curly">{</span>
            </div>
            <div class="line">
                {$this->printItems($type->getFields())}
            </div>
            <div class="line">
                <span class="bracket-curly">}</span>
            </div>
        </section>
        EOL;
    }

    public function visitInterface(\Graphpinator\Type\InterfaceType $interface) : string
    {
        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($interface->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$interface->getName()}">interface</span>&nbsp;
                <span class="typename">{$interface->getName()}</span>
                <span class="implements">{$this->printImplements($interface->getInterfaces())}</span>
                <span class="usage">{$this->printDirectiveUsages($interface->getDirectiveUsages())}</span>&nbsp;
                <span class="bracket-curly">{</span>
            </div>
            <div class="line">
                {$this->printItems($interface->getFields())}
            </div>
            <div class="line">
                <span class="bracket-curly">}</span>
            </div>
        </section>
        EOL;
    }

    public function visitUnion(\Graphpinator\Type\UnionType $union) : string
    {
        $typeNames = [];

        foreach ($union->getTypes() as $type) {
            $normalizedDescription = static::normalizeString($type->getDescription());
            $typeNames[] = '<a class="union-type" href="#graphql-type-' . $type->getName() . '" title="' . $normalizedDescription . '">' . $type->getName() . '</a>';
        }

        $types = \implode('&nbsp;<span class="vertical-line">|</span>&nbsp;', $typeNames);

        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($union->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$union->getName()}">union</span>&nbsp;
                <span class="typename">{$union->getName()}&nbsp;<span class="equals">=</span>&nbsp;{$types}</span>
            </div>
        </section>
        EOL;
    }

    public function visitInput(\Graphpinator\Type\InputType $input) : string
    {
        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($input->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$input->getName()}">input</span>&nbsp;
                <span class="typename">{$input->getName()}</span>
                <span class="usage">{$this->printDirectiveUsages($input->getDirectiveUsages())}</span>&nbsp;
                <span class="bracket-curly">{</span>
            </div>
            <div class="line offset-1">
                {$this->printItems($input->getArguments())}
            </div>
            <div class="line">
                <span class="bracket-curly">}</span>
            </div>
        </section>
        EOL;
    }

    public function visitScalar(\Graphpinator\Type\ScalarType $scalar) : string
    {
        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($scalar->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$scalar->getName()}">scalar</span>&nbsp;
                <span class="typename">{$scalar->getName()}</span>
            </div>
        </section>
        EOL;
    }

    public function visitEnum(\Graphpinator\Type\EnumType $enum) : string
    {
        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($enum->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$enum->getName()}">enum</span>&nbsp;
                <span class="typename">{$enum->getName()}</span>&nbsp;
                <span class="bracket-curly">{</span>
            </div>
            <div class="line offset-1">
                {$this->printItems($enum->getItems())}
            </div>
            <div class="line">
                <span class="bracket-curly">}</span>
            </div>
        </section>
        EOL;
    }

    public function visitDirective(\Graphpinator\Directive\Directive $directive) : string
    {
        $directiveAdditional = $this->printArguments($directive);

        if ($directive->isRepeatable()) {
            $directiveAdditional .= '&nbsp;<span class="keyword">repeatable</span>';
        }

        $directiveAdditional .= '&nbsp;<span class="keyword">on</span>&nbsp;';
        $directiveAdditional .= '<span class="location">' . \implode('&nbsp;<span class="vertical-line">|</span>&nbsp;', $directive->getLocations()) . '</span>';

        return <<<EOL
        <section>
            <div class="line">
                <span class="description">{$this->printDescription($directive->getDescription())}</span>
                <span class="keyword" id="graphql-type-{$directive->getName()}">directive</span>&nbsp;
                <span class="typename">@{$directive->getName()}</span>
                {$directiveAdditional}
            </div>
        </section>
        EOL;
    }

    public function visitField(\Graphpinator\Field\Field $field) : string
    {
        $normalizedDescription = static::normalizeString($field->getType()->getNamedType()->getDescription());
        $name = static::normalizePunctuation($field->getType()->printName());

        return <<<EOL
        <div class="line offset-1">
            <span class="description">{$this->printItemDescription($field->getDescription())}</span>
            <span class="field-name">{$field->getName()}</span>
            <div class="arguments">{$this->printArguments($field)}</div>
            <span class="colon">:</span>&nbsp;
            <a class="field-type" href="#graphql-type-{$field->getType()->getNamedType()->printName()}" title="{$normalizedDescription}">{$name}</a>
            {$this->printDirectiveUsages($field->getDirectiveUsages())}
        </div>
        EOL;
    }

    public function visitArgument(\Graphpinator\Argument\Argument $argument) : string
    {
        $defaultValue = '';
        $name = static::normalizePunctuation($argument->getType()->printName());
        $normalizedDescription = static::normalizeString($argument->getType()->getNamedType()->getDescription());

        if ($argument->getDefaultValue() instanceof \Graphpinator\Value\ArgumentValue) {
            $defaultValue .= '&nbsp;<span class="equals">=</span>&nbsp;';
            $defaultValue .= '<span class="argument-value">' . $this->printValue($argument->getDefaultValue()->getValue()) . '</span>';
        }

        return <<<EOL
            <span class="description">{$this->printItemDescription($argument->getDescription())}</span>
            <span class="argument-name">{$argument->getName()}</span>
            <span class="colon">:</span>&nbsp;
            <a class="argument-type" href="#graphql-type-{$argument->getType()->getNamedType()->printName()}" title="{$normalizedDescription}">{$name}</a>
            {$defaultValue}
            {$this->printDirectiveUsages($argument->getDirectiveUsages())}
        EOL;
    }

    public function visitDirectiveUsage(\Graphpinator\DirectiveUsage\DirectiveUsage $directiveUsage) : string
    {
        $schema = '&nbsp;<span class="typename">@' . $directiveUsage->getDirective()->getName() . '</span>';
        $printableArguments = [];

        foreach ($directiveUsage->getArgumentValues() as $argument) {
            // do not print default value
            if ($argument->getValue()->getRawValue() === $argument->getArgument()->getDefaultValue()?->getValue()->getRawValue()) {
                continue;
            }

            $printableArgument = '<span class="directive-usage-name">' . $argument->getArgument()->getName() . '</span>';
            $printableArgument .= '<span class="colon">:</span>&nbsp;';
            $printableArgument .= '<span class="directive-usage-value">' . static::normalizePunctuation($argument->getValue()->printValue()) . '</span>';

            $printableArguments[] =  $printableArgument;
        }

        if (\count($printableArguments)) {
            $schema .= '<span class="bracket-round">(</span>' . \implode('<span class="comma">,</span>&nbsp;', $printableArguments) . '<span class="bracket-round">)</span>';
        }

        return $schema;
    }

    public function visitEnumItem(\Graphpinator\EnumItem\EnumItem $enumItem) : string
    {
        $normalizedDescription = static::normalizeString($enumItem->getDescription());

        return '<span class="enum-item line" title="' . $normalizedDescription . '">' . $enumItem->getName()
            . $this->printDirectiveUsages($enumItem->getDirectiveUsages()) . '</span>';
    }

    public function glue(array $entries) : string
    {
        return \preg_replace(['/\>\s+\</', '/\>\s*(&nbsp;)?\s+\</'], ['><', '>&nbsp;<'], \implode('', $entries));
    }

    private function printImplements(\Graphpinator\Type\InterfaceSet $implements) : string
    {
        if (\count($implements) === 0) {
            return '';
        }

        return '&nbsp;implements&nbsp;' . \implode('&nbsp;<span class="ampersand">&</span>&nbsp;', self::recursiveGetInterfaces($implements));
    }

    /**
     * @return array<string>
     */
    private static function recursiveGetInterfaces(\Graphpinator\Type\InterfaceSet $implements) : array
    {
        $return = [];

        foreach ($implements as $interface) {
            $normalizedDescription = static::normalizeString($interface->getDescription());
            $return += self::recursiveGetInterfaces($interface->getInterfaces());
            $return[] = '<a class="typename" href="#graphql-type-' . $interface->getName() . '" title="' . $normalizedDescription . '">' . $interface->getName() . '</a>';
        }

        return $return;
    }

    private function printDirectiveUsages(\Graphpinator\DirectiveUsage\DirectiveUsageSet $set) : string
    {
        $return = '';

        foreach ($set as $directiveUsage) {
            $return .= $directiveUsage->accept($this);
        }

        return $return;
    }

    private function printItems(
        \Graphpinator\Field\FieldSet|\Graphpinator\Argument\ArgumentSet|\Graphpinator\EnumItem\EnumItemSet $set,
    ) : string
    {
        $result = '';
        $previousHasDescription = false;
        $isFirst = true;

        foreach ($set as $item) {
            $currentHasDescription = $item->getDescription() !== null;

            if (!$isFirst && ($previousHasDescription || $currentHasDescription)) {
                $result .= '<br>';
            }

            $result .= '<div class="item">' . $item->accept($this) . '</div>';
            $previousHasDescription = $currentHasDescription;
            $isFirst = false;
        }

        return $result;
    }

    private function printLeafValue(\Graphpinator\Value\InputedValue $value) : string
    {
        $className = '';

        if ($value instanceof \Graphpinator\Value\NullValue) {
            $className = 'null';
        }

        if ($value instanceof \Graphpinator\Value\EnumValue) {
            $className = 'enum-literal';
        }

        if ($value instanceof \Graphpinator\Value\ScalarValue) {
            $rawValue = $value->getRawValue();

            if (\is_bool($rawValue)) {
                $className = $rawValue ? 'true' : 'false';
            }

            if (\is_int($rawValue)) {
                $className = 'int-literal';
            }

            if (\is_float($rawValue)) {
                $className = 'float-literal';
            }
        }

        return '<span class="' . $className . '">' . $value->printValue() . '</span>';
    }

    private function printValue(\Graphpinator\Value\InputedValue $value) : string
    {
        if ($value instanceof \Graphpinator\Value\LeafValue || $value instanceof \Graphpinator\Value\NullValue) {
            return $this->printLeafValue($value);
        }

        $component = [];

        if ($value instanceof \Graphpinator\Value\InputValue) {
            $openingChar = '<span class="bracket-curly">{</span>';
            $closingChar = '<span class="bracket-curly">}</span>';

            foreach ($value as $key => $innerValue) {
                \assert($innerValue instanceof \Graphpinator\Value\ArgumentValue);

                $component[] = '<span class="value-name">' . $key . '</span><span class="colon">:</span>' . $this->printValue($innerValue->getValue());
            }
        } elseif ($value instanceof \Graphpinator\Value\ListInputedValue) {
            $openingChar = '<span class="bracket-square">[</span>';
            $closingChar = '<span class="bracket-square">]</span>';

            foreach ($value as $innerValue) {
                \assert($innerValue instanceof \Graphpinator\Value\InputedValue);

                $component[] = $this->printValue($innerValue);
            }
        } else {
            throw new \InvalidArgumentException('Unknown value type.');
        }

        if (\count($component) === 0) {
            return $openingChar . $closingChar;
        }

        $components = \implode('<span class="comma">,</span>', $component);

        return $openingChar . '<span class="value">' . $components . '</span>' . $closingChar;
    }

    private function printArguments(\Graphpinator\Directive\Directive|\Graphpinator\Field\Field $component) : string
    {
        $toReturn = '';

        if ($component->getArguments()->count() > 0) {
            $toReturn .= '<span class="bracket-round">(</span>';
            $toReturn .= '<div class="line offset-1">' . $this->printItems($component->getArguments()) . '</div>';
            $toReturn .= '<span class="bracket-round">)</span>';
        }

        return $toReturn;
    }

    private function printDescription(?string $description) : string
    {
        if ($description === null) {
            return '';
        }

        return '"""<br>' . $description .  '<br>"""<br>';
    }

    private function printItemDescription(?string $description) : string
    {
        if ($description === null) {
            return '';
        }

        if (!\str_contains($description, \PHP_EOL)) {
            return '"' . $description . '"<br>';
        }

        return '"""<br>' . \str_replace(\PHP_EOL, '<br>', $description) . '<br>"""<br>';
    }

    private static function normalizeString(?string $input) : string
    {
        return \is_string($input)
            ? \htmlspecialchars($input)
            : '';
    }

    private static function normalizePunctuation(string $input) : string
    {
        $input = \str_replace('!', '<span class="exclamation-mark">!</span>', $input);
        $input = \str_replace('[', '<span class="bracket-square">[</span>', $input);
        $input = \str_replace(']', '<span class="bracket-square">]</span>', $input);

        return $input;
    }
}
