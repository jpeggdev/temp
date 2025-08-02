<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * Standalone implementation of PostgreSQL text search matching (@@).
 *
 * Example usage in DQL:
 *    TSMATCH(TO_TSVECTOR('english', e.someField), TO_TSQUERY('english', :query)) = TRUE
 *
 * That translates to SQL:
 *    (to_tsvector('english', e.someField) @@ to_tsquery('english', :query)) = TRUE
 *
 * Or, if you have a tsvector column:
 *    TSMATCH(e.searchVector, TO_TSQUERY('english', :query)) = TRUE
 *
 * Must be used in a boolean expression:
 *    WHERE TSMATCH(...) = TRUE
 *
 * @see https://www.postgresql.org/docs/current/textsearch-controls.html
 */
class Tsmatch extends FunctionNode
{
    private ?Node $firstArgument = null;
    private ?Node $secondArgument = null;

    /**
     * Parses the custom DQL function syntax:
     *   TSMATCH(<arg1>, <arg2>)
     */
    public function parse(Parser $parser): void
    {
        // 1) The function name 'TSMATCH'
        $parser->match(TokenType::T_IDENTIFIER);

        // 2) An opening parenthesis '('
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        // 3) Parse the first argument (e.g. TO_TSVECTOR(...) or a field)
        $this->firstArgument = $parser->StringPrimary();

        // 4) A comma ','
        $parser->match(TokenType::T_COMMA);

        // 5) Parse the second argument (e.g. TO_TSQUERY(...) or a parameter)
        $this->secondArgument = $parser->StringPrimary();

        // 6) A closing parenthesis ')'
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * Produces the final SQL for "(arg1 @@ arg2)".
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $arg1 = $this->firstArgument->dispatch($sqlWalker);
        $arg2 = $this->secondArgument->dispatch($sqlWalker);

        return sprintf('(%s @@ %s)', $arg1, $arg2);
    }
}
