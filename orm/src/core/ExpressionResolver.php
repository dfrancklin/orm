<?php

namespace ORM\Core;

class ExpressionResolver {

	public static function get($expression, $comment, $all = false) {
		$expression = constant(OrmExpressions::class . '::' . $expression);
		$comment = self::stripChars($comment);

		if ($all) {
			return self::all($expression, $comment);
		} else {
			return self::match($expression, $comment);
		}
	}

	private static function all($expression, $comment) {
		preg_match_all($expression, $comment, $matches);

		if (isset($matches[0])) {
			return join('', $matches[0]);
		} else {
			return null;
		}
	}

	private static function match($expression, $comment) {
		preg_match($expression, $comment, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		} elseif (isset($matches[0])) {
			return $matches[0];
		} else {
			return null;
		}
	}

	public static function stripChars($comment) {
		$comment = preg_replace("/\n?@ORM/i", "|@ORM", $comment, -1, $count);
		$comment = preg_replace("/(\/\*|\*\/|\*|\s+)*/i", "", $comment);
		$comment = trim(preg_replace("/\|/i", "\n", $comment));

		return $comment;
	}

}
