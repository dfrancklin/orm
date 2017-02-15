<?php
namespace ORM\Core;

class OrmExpressions {

	const
		any = '\\\\@A-Za-z0-9=,_\(\)\s',
		anyWithBraces = '\\\\A-Za-z0-9=,_\(\)\{\}\s',

		// Annotations
		orm = '/@ORM\/[' . self::anyWithBraces . ']+/i',
		entity = '/Entity/i',
		id = '/Id/i',
		generated = '/Generated/i',
		table = '/Table\(.+\)/i',
		column = '/Column\(.+\)/i',
		transient = '/Transient/i',
			// Joins
			hasOne = '/HasOne\([' . self::any . ']+\)/i',
			hasMany = '/HasMany\([' . self::any . ']+\)/i',
			belongsTo = '/BelongsTo\([' . self::any . ']+\)/i',

			// oneToOne = '/OneToOne/i',
			// oneToMany = '/OneToMany\(.+\)/i',
			// manyToOne = '/ManyToOne\(.+\)/i',
			// manyToMany = '/ManyToMany\(.+\)/i',

			joinColumn = '/@JoinColumn\(.+\)/i',
			joinColumns = '/@JoinColumns\(\{(.+)\}\)/i',
			joinTable = '/@JoinTable\(.+\)/i',

		// Attributes of annotations
		name = '/name[\s]?=[\s]?(\w+)/i',
		type = '/type[\s]?=[\s]?(\w+)/i',
		length = '/length[\s]?=[\s]?(\d+)/i',
		nullable = '/nullable[\s]?=[\s]?(\w+)/i',
		unique = '/unique[\s]?=[\s]?(\w+)/i',
			// Attributes from joins
			tableName = '/tableName[\s]?=[\s]?(\w+)/i',
			joinTableJoinColumns = '/joinColumns[\s]?=[\s]?\{[\s]?([' . self::any . ']*)[\s]?\}/i',
			inverseJoinColumns = '/inverseJoinColumns[\s]?=[\s]?\{[\s]?([' . self::any . ']*)[\s]?\}/i',
			referencedColumnName = '/referencedColumnName[\s]?=[\s]?(\w+)/i',
			mappedBy = '/mappedBy[\s]?=[\s]?(\w+)/i',
			className = '/class[\s]?=[\s]?([\\\\\w]+)/i';

}