<?php

declare(strict_types = 1);

namespace Graphpinator\Printer;

class TypeKindSorter implements \Graphpinator\Printer\Sorter
{
    /**
     * @param array<\Graphpinator\Type\Contract\NamedDefinition> $types
     * @param array<\Graphpinator\Directive\Contract\Definition> $directives
     * @return array<\Graphpinator\Type\Contract\NamedDefinition|\Graphpinator\Directive\Contract\Definition>
     */
    public function sort(array $types, array $directives) : array
    {
        $interface = $union = $input = $enum = $scalar = $object = [];

        foreach ($types as $name => $type) {
            match ($type->accept(new \Graphpinator\Introspection\TypeKindVisitor())) {
                \Graphpinator\Introspection\TypeKind::INTERFACE => $interface[$name] = $type,
                \Graphpinator\Introspection\TypeKind::UNION => $union[$name] = $type,
                \Graphpinator\Introspection\TypeKind::INPUT_OBJECT => $input[$name] = $type,
                \Graphpinator\Introspection\TypeKind::ENUM => $enum[$name] = $type,
                \Graphpinator\Introspection\TypeKind::SCALAR => $scalar[$name] = $type,
                \Graphpinator\Introspection\TypeKind::OBJECT => $object[$name] = $type,
                default => null,
            };
        }

        \ksort($interface);
        \ksort($union);
        \ksort($input);
        \ksort($enum);
        \ksort($scalar);
        \ksort($object);
        \ksort($directives);

        return \array_merge($interface, $object, $union, $input, $scalar, $enum, $directives);
    }
}
