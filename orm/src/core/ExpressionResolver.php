<?php
namespace ORM\Core;

class ExpressionResolver {

	public static function get($expression, $comment, $all = false) {
		if ($all) {
			return self::all($expression, $comment);
		} else {
			return self::match($expression, $comment);
		}
	}

	private static function all($expression, $comment) {
		preg_match_all(constant(OrmExpressions::class . '::' . $expression), $comment, $matches);

		if (isset($matches[0])) {
			return join('', $matches[0]);
		} else {
			return null;
		}
	}

	private static function match($expression, $comment) {
		preg_match(constant(OrmExpressions::class . '::' . $expression), $comment, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		} elseif (isset($matches[0])) {
			return $matches[0];
		} else {
			return null;
		}
	}

}