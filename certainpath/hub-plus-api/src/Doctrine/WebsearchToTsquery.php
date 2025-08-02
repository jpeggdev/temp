<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * Custom DQL function for PostgreSQL's websearch_to_tsquery(...).
 *
 * Usage examples in DQL:
 *  - WEBCSEARCH_TO_TSQUERY(:text)
 *  - WEBCSEARCH_TO_TSQUERY('english', :text)
 *
 * This interprets the text in a "Google-like" fashion, supporting:
 *  - Quoted phrases ("brand new")
 *  - + and - operators
 *  - | for OR
 *
 * @see https://www.postgresql.org/docs/current/textsearch-controls.html#TEXTSEARCH-WEBSEARCH
 */
class WebsearchToTsquery extends FunctionNode
{
    /**
     * Holds the arguments we parse. Could be 1 or 2, depending on usage.
     * For example:
     *  - [Node for 'english', Node for :searchTerm]
     *  - [Node for :searchTerm].
     *
     * @var Node[]
     */
    private array $arguments = [];

    /**
     * Doctrine calls parse() to let you parse the DQL function and its arguments.
     */
    public function parse(Parser $parser): void
    {
        $lexer = $parser->getLexer();

        // 1) The function name (WEBCSEARCH_TO_TSQUERY)
        $parser->match(TokenType::T_IDENTIFIER);

        // 2) An opening parenthesis '('
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        // 3) Parse the first argument
        $this->arguments[] = $parser->StringPrimary();

        // 4) Check if there's a comma => parse second argument
        if (null !== $lexer->lookahead && TokenType::T_COMMA === $lexer->lookahead->type) {
            $parser->match(TokenType::T_COMMA);
            $this->arguments[] = $parser->StringPrimary();
        }

        // 5) A closing parenthesis ')'
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);

        // 6) Validate that we have 1 or 2 arguments
        $count = \count($this->arguments);
        if ($count > 2) {
            throw new \InvalidArgumentException(\sprintf('WEBCSEARCH_TO_TSQUERY() supports 1 or 2 arguments, but got %d.', $count));
        }
    }

    /**
     * Doctrine calls getSql() to convert your parsed arguments into real SQL.
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        // Convert each argument's AST node into SQL
        $sqlArgs = [];
        foreach ($this->arguments as $argNode) {
            $sqlArgs[] = $argNode->dispatch($sqlWalker);
        }

        // If there's only 1 argument => websearch_to_tsquery(arg1)
        // If 2 => websearch_to_tsquery(arg1, arg2)
        return sprintf('websearch_to_tsquery(%s)', implode(', ', $sqlArgs));
    }
}
