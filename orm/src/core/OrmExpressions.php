<?php
namespace ORM\Core;

class OrmExpressions {

	const
		any = '\\\\@A-Za-z0-9=,_\/\s\(\)',
		anyWithBraces = self::any . '\{\}',

		// Annotations
		orm = '/@ORM\/[' . self::anyWithBraces . ']+/i',
		entity = '/Entity/i',
		id = '/@ORM\/(Id)/i',
		generated = '/Generated/i',
		table = '/Table\([' . self::any . ']+\)/i',
		column = '/Column\([' . self::any . ']+\)/i',
		transient = '/Transient/i',
			// Joins
			hasOne = '/HasOne\([^\)]+\)/i',
			hasMany = '/HasMany\([^\)]+\)/i',
			manyToMany = '/ManyToMany\([^\)]+\)/i',
			belongsTo = '/BelongsTo\([^\)]+\)/i',
			joinColumn = '/JoinColumn\([' . self::any . ']+\)/i',
			joinTable = '/JoinTable\(.+\)/i',

		// Attributes of annotations
		name = '/name[\s]?=[\s]?(\w+)/i',
		type = '/type[\s]?=[\s]?(\w+)/i',
		length = '/length[\s]?=[\s]?(\d+)/i',
		scale = '/scale[\s]?=[\s]?(\d+)/i',
		precision = '/precision[\s]?=[\s]?(\d+)/i',
		nullable = '/nullable[\s]?=[\s]?(\w+)/i',
		unique = '/unique[\s]?=[\s]?(\w+)/i',
		// Attributes from joins
			tableName = '/tableName[\s]?=[\s]?(\w+)/i',
			join = '/join[\s]?=[\s]?\{([' . self::any . ']*)\}/i',
			inverse = '/inverse[\s]?=[\s]?\{([' . self::any . ']*)\}/i',
			// reference = '/reference[\s]?=[\s]?(\w+)/i',
			mappedBy = '/mappedBy[\s]?=[\s]?(\w+)/i',
			className = '/class[\s]?=[\s]?([\\\\\w]+)/i',
			cascade = '/cascade[\s]?=[\s]?\{([A-Za-z,\s]+)\}/i',
			optional = '/optional[\s]?=[\s]?(\w+)/i';

}
