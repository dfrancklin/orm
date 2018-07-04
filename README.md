# _Lumberjack's ORM_

## Intodução

_Lumberjack's ORM_ é um _framework ORM_ para a linguagem _PHP_. _ORM_ é uma sigla em inglês que significa _Object-Relational Mapper_. Um _ORM_ é uma ferramenta bastante útil no dia-a-dia do desenvolvedor de _software_.

O _Lumberjack's ORM_ trabalha com mapeamento de tabelas em classes do modelo de dados utilizando _Annotations_. O _ORM_ permite ao desenvolvedor trabalhar com mais de uma conexão de banco de dados e criar as tabelas do banco de dados baseado nas classes do modelo.

## Tabela de Conteúdos

- [1. INSTALAÇÃO](#1-instalaÇÃo)
- [2. DEFININDO CONEXÕES](#2-definindo-conexÕes)
	- [2.1. Criar Tabelas Automaticamente](#21-criar-tabelas-automaticamente)
- [3. DEFININDO MODELOS](#3-definindo-modelos)
	- [3.1. _Annotations_](#31-annotations)
		- [3.1.1. _Annotations_ de Classes](#311-annotations-de-classes)
		- [3.1.2. _Annotations_ de Propriedades](#312-annotations-de-propriedades)
		- [3.1.3. _Annotations_ de Relacionamentos](#313-annotations-de-relacionamentos)
	- [3.2. Exemplo de Mapeamento Simples](#32-exemplo-de-mapeamento-simples)
	- [3.3. Exemplos de Mapeamento de Relacionamentos](#33-exemplos-de-mapeamento-de-relacionamentos)
		- [3.3.1. Um para Um](#331-um-para-um)
		- [3.3.2. Um para Muitos](#332-um-para-muitos)
		- [3.3.3. Muitos para Muitos](#333-muitos-para-muitos)
- [4. _ENTITY MANAGER_](#4-entity-manager)
	- [4.1. Obtendo Uma Instância do _EntityManager_](#41-obtendo-uma-instância-do-entitymanager)
	- [4.2. Transações](#42-transaÇÕes)
		- [4.2.1. _BeginTransaction_](#421-begintransaction)
		- [4.2.2. _Commit_](#422-commit)
		- [4.2.3. _Rollback_](#423-rollback)
	- [4.3. _Find_](#43-find)
	- [4.4. _List_](#44-list)
	- [4.5. _QueryBuilder_](#45-querybuilder)
		- [4.5.1. Obtendo o _QueryBuilder_](#451-obtendo-o-querybuilder)
		- [4.5.2. Criando uma consulta simples usando _list_](#452-criando-uma-consulta-simples-usando-list)
		- [4.5.3. Criando uma consulta simples usando _one_](#453-criando-uma-consulta-simples-usando-one)
		- [4.5.4. _Join_](#454-join)
		- [4.5.5. _Where_](#455-where)
		- [4.5.6. Paginação](#456-paginaÇÃo)
		- [4.5.7. _OrderBy_](#457-orderby)
		- [4.5.8. _GroupBy_](#458-groupby)
		- [4.5.9. Agregação](#459-agregaÇÃo)
		- [4.5.10. _Having_](#4510-having)
	- [4.6. _Save_](#46-save)
	- [4.7. _Remove_](#47-remove)
- [5. _LOGGER_](#5-logger)
- [6. _DRIVER_](#6-driver)

## 1. INSTALAÇÃO

Para usar o _Lumberjack's ORM_, pode-se obtê-lo no repositório orm no __GitHub__ no link: https://github.com/dfrancklin/orm.

Basta então copiar a pasta `/orm` que foi baixada e para incluir o _ORM_ no projeto, use o comando `require_once`:

__Código Exemplo 1:__ Incluir o _ORM_ ao projeto

```php
<?php
    require_once './orm/load.php';
?>
```

[Voltar](#tabela-de-conteúdos)

## 2. DEFININDO CONEXÕES

As conexões que serão utilizadas pelo _ORM_ devem ser declaradas em um arquivo com extensão `.php`. Por padrão, o arquivo é esperado que esteja na pasta raiz do _ORM_ com o nome `connection.config.php`, ou seja, supondo que o _ORM_ esteja localizado `/home/user/app/orm/`, então o caminho para o arquivo seria `/home/user/app/orm/connection.config.php`.

O arquivo de conexões pode ser substituído da seguinte maneira:

__Código Exemplo 2:__ Definindo qual arquivo de conexões o _ORM_ utilizará

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnectionsFile(__DIR__ . '/db/connections.php');
?>
```

O arquivo deve conter um _array_ com uma ou mais conexões, onde a chave da conexão é o nome identificador da conexão e o valor é um _array_ contendo as informações da conexão. As informações variam de acordo com o banco de dados a ser utilizado.

__Código Exemplo 3:__ Definindo arquivo de conexões

```php
<?php
    return [
        'exemplo-mysql' => [
            'db' => 'mysql',
            'version' => '5.7.11',
            'host' => 'localhost',
            'schema' => 'app',
            'user' => 'root'
            'pass' => 'root'
        ],
        'exemplo-sqlite' => [
            'db' => 'sqlite',
            'version' => '3',
            'file' => '../data/app-storage.sq3',
        ],
    ];
?>
```

Os valores para a conexão `exemplo-mysql` é um _array_ contendo as chaves `db`, `version`, `host`, `schema`, `user` e `pass`. A chave `db` contém o banco de dados a ser utilizado. A chave `version` indica a versão do banco de dados utilizado, no qual o _Driver_ deve corresponder à essa versão. A chave `host` é o endereço onde o banco de dados está localizado. A chave `schema` é o banco de dados (conjunto de tabelas) que será utilizado. As chaves `user` e `pass` são respectivamente o usuário e a senha de acesso ao banco de dados.

Os valores para a conexão `exemplo-sqlite` é um _array_ contendo as chaves `db`, `version`, `file`. As chaves `db` e `version` funcionam da mesma maneira que a conexão anterior. A chave `file` indica o arquivo local o qual o banco de dados _SQLite_ utilizará para armazenar os dados.

Para informar ao _ORM_ qual (ou quais) conexão será utilizada na aplicação, deve ser feito através da classe principal do _ORM_ conforme o exemplo a seguir:

__Código Exemplo 4:__ Definindo conexões que o _ORM_ poderá utilizar

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $orm->addConnection('exemplo-sqlite');
?>
```

O método _setConnection_ adiciona a conexão à lista de conexões que o _ORM_ pode utilizar e faz com que a conexão informada seja a conexão padrão para o _ORM_, ou seja, qualquer operação que será realizada pelo _ORM_, se não for informada uma conexão explicitamente, o _ORM_ irá assumir que a conexão que precisa ser usada é a conexão padrão.

Já o método _addConnection_ apenas adiciona a conexão à lista de conexões que o _ORM_ pode utilizar. A conexão padrão pode ser substituída a qualquer momento, para isso basta utilizar o método _setDefaultConnection_:
 
__Código Exemplo 5:__ Definindo conexões que o _ORM_ poderá utilizar

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->addConnection('exemplo-mysql');
    $orm->setDefaultConnection('exemplo-mysql');
?>
```

[Voltar](#tabela-de-conteúdos)

### 2.1. Criar Tabelas Automaticamente
------

O _ORM_ tem a habilidade de criar as tabelas a partir das classes modelo. Para que o _ORM_ saiba como criar, é necessário informar o caminho para a pasta que contém os modelos e o _namespace_ no momento em que estiver configurando a conexão no _ORM_. Por exemplo:

__Código Exemplo 6:__ Definindo criação de tabelas no _ORM_

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql', [
        'namespace' => 'App\\Models',
        'modelsFolder' => '/home/user/app/models',
        'create' => true
    ]);
?>
```

Pode ser necessário também, apagar as tabelas antes de criá-las, para isso, basta informar também na configuração da conexão:

__Código Exemplo 7:__ Definindo criação e deleção de tabelas no _ORM_

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql', [
        'namespace' => 'App\\Models',
        'modelsFolder' => '/home/user/app/models',
        'create' => true,
        'drop' => true
    ]);
?>
```

O _ORM_ permite ainda, que uma ação seja executada antes de apagar as tabelas e uma ação após criar as tabelas. Essas ações podem ser úteis para criar uma rotina de _backup/restore_ ou de migração de banco de dados. Para informar ao _ORM_ quais ações ele deve executar, basta fazer o seguinte:

__Código Exemplo 8:__ Definindo ação para executar antes criação e da deleção de tabelas no _ORM_

```php
<?php
    $dbHelper = new App\Helpers\InitDatabase();
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql', [
        'namespace' => 'App\\Models',
        'modelsFolder' => '/home/user/app/models',
        'drop' => true,
        'create' => true,
        'beforeDrop' => [ $dbHelper, 'beforeDrop' ],
        'afterCreate' => [ $dbHelper, 'afterCreate' ]
    ]);
?>
```

Na linha 2, é criada uma instância da classe `App\Helpers\InitDatabase` e nas linhas 9 e 10, são informados, para a conexão respectivamente, quais métodos devem ser executados antes de apagar as tabelas e depois de criá-las. Utilizando esses métodos é possível que o desenvolvedor crie uma lógica de como realizar o _backup_ das informações essenciais do banco de dados antes de apagar as tabelas e posteriormente restaurar essas informações após a criação das tabelas.

Os valores esperados pelas chaves `beforeDrop` e `afterCreate` podem ser também uma função anônima:

__Código Exemplo 9:__ Definindo ação para executar antes criação e da deleção de tabelas no _ORM_

```php
<?php
    ...
        'beforeDrop' => function($entityManager) { ... },
        'afterCreate' => function($entityManager) { ... }
    ...
?>
```

Ou uma `string` contendo o nome de uma função:

__Código Exemplo 10:__ Definindo ação para executar antes criação e da deleção de tabelas no _ORM_

```php
<?php
    ...
        'beforeDrop' => 'beforeDrop',
        'afterCreate' => 'afterCreate'
    ...

    function beforeDrop($entityManager) { ... }
    function afterCreate($entityManager) { ... }
?>
```

O _ORM_ passa uma instância de um _EntityManager_ por parâmetro para os métodos ou funções que serão executadas antes e depois do processo de criação das tabelas. Ele pode ser usado para realizar ações no banco de dados. O _EntityManager_ será abordado mais à frente.

[Voltar](#tabela-de-conteúdos)

## 3. DEFININDO MODELOS

Um modelo é uma classe que representa uma tabela no banco de dados e pode ser mapeada da classe para a tabela e da tabela para a classe em operações de consulta, inserção, alteração e deleção.

Para que um modelo possa representar devidamente uma tabela no banco de dados dentro do _ORM_, ela deve ser "anotada" utilizando o padrão de _annotation_ definido pelo _ORM_.

[Voltar](#tabela-de-conteúdos)

### 3.1. _Annotations_
------

As _annotations_ são "etiquetas" que adicionam metadados relevantes sobre classes, métodos e propriedades. Ou seja, através do uso de _annotations_, pode-se adicionar às classes informações para mapear tabelas do banco de dados, e adicionar às propriedades da classe para mapear as colunas de uma tabela do banco de dados, para que posteriormente, em tempo de execução, os metadados indicados pelas _annotations_ sejam analisados e a partir disso, o _ORM_ irá trabalhar de acordo com essas informações.

Abaixo, uma lista completa das _annotations_ e suas propriedades:

[Voltar](#tabela-de-conteúdos)

#### 3.1.1. _Annotations_ de Classes
------

- __*Annotation:*__ `@ORM/Entity`.

	__Descrição:__ Define que a classe deve ser considerada como uma tabela no banco de dados.

	__Preenchimento:__ Obrigatório. O não preenchimento resulta em erro.

- __*Annotation:*__ `@ORM/Table`.

	__Descrição:__ Define informações sobre a tabela mapeada.

	__Preenchimento:__ Opcional.

	__Propriedades:__

	- __Nome:__ `name`.

		__Descrição:__ Define o nome da tabela mapeada.

		__Preenchimento:__ Opcional. Caso não preenchido, o _ORM_ assume que o nome da tabela é o mesmo que o nome da classe.

	- __Nome:__ `schema`.

		__Descrição:__ Define qual é o conjunto de tabelas ou banco de dados ao qual a tabela mapeada em questão existe.

		__Preenchimento:__ Opcional. Caso não preenchido, o _ORM_ assume que o `schema` a ser usado é o padrão definido na conexão ou nenhum, dependendo do banco de dados usado.

	- __Nome:__ `mutable`.

		__Descrição:__ Caso o valor seja `true` define que a tabela não pode ser modificada pelas operações de inserção, alteração e deleção. O valor padrão é `false`. Normalmente utilizado para mapear uma view.

		__Preenchimento:__ Opcional. Assume o valor padrão caso não seja preenchido.


[Voltar](#tabela-de-conteúdos)

#### 3.1.2. _Annotations_ de Propriedades
------

- __*Annotation:*__ `@ORM/Id`.

	__Descrição:__ Define que a propriedade representa a chave primária da tabela mapeada.

	__Preenchimento:__ Obrigatório. O não preenchimento resulta em erro.

- __*Annotation:*__ `@ORM/Generated`.

	__Descrição:__ Define que o valor da chave primaria é auto gerado, seja através de sequence ou qualquer tipo de `autoincrement` (isso é definido no driver para cada banco de dados).

	__Preenchimento:__ Opcional. O não preenchimento indica que o preenchimento e o incremento deverão ser feitos manualmente.

- __*Annotation:*__ `@ORM/Column`.

	__Descrição:__ Define informações sobre a coluna ser mapeada.

	__Preenchimento:__ Opcional. Assume os valores padrões das propriedades listadas a seguir.

	__Propriedades:__

	- __Nome:__ `name`.

		__Descrição:__ Define o nome da coluna a ser mapeada.

		__Preenchimento:__ Opcional. Caso não seja preenchido, o _ORM_ assume que o nome da coluna é o mesmo nome do atributo.

	- __Nome:__ `type`.

		__Descrição:__ Define o tipo da coluna a ser mapeada.

		__Tipos:__ `string`, `int`, `float`, `lob` (_large object_), `date`, `time`, `datetime`, `bool`.

		__Preenchimento:__ Opcional. Caso não seja preenchido, o _ORM_ assume que o tipo da coluna é `string`.

	- __Nome:__ `length`.

		__Descrição:__ Define o tamanho da coluna a ser mapeada quando a coluna é do tipo `string`.

		__Preenchimento:__ Opcional. Caso não seja preenchido, o _ORM_ assume que o tamanho da coluna é 255.

	- __Nome:__ `scale`.

		__Descrição:__ Define o tamanho da coluna a ser mapeada quando a coluna é do tipo `float`.

		__Preenchimento:__ Opcional. Caso não seja preenchido, o _ORM_ assume que o tamanho da coluna é 14.

	- __Nome:__ `precision`.

		__Descrição:__ Define a precisão da coluna (quantidade de dígitos após a virgula) a ser mapeada quando a coluna é do tipo `float`.

		__Preenchimento:__ Opcional. Caso não seja preenchido, o _ORM_ assume que a precisão da coluna é 2.

	- __Nome:__ `unique`.

		__Descrição:__ Se o valor do campo for `true`, define que o campo deve ser conter um valor único.

		__Preenchimento:__ Opcional. Valor padrão é `false`.

	- __Nome:__ `nullable`.

		__Descrição:__ Se o valor do campo for `false`, define que o campo não pode receber valores nulos.

		__Preenchimento:__ Opcional. Valor padrão é `true`.


[Voltar](#tabela-de-conteúdos)

#### 3.1.3. _Annotations_ de Relacionamentos
------

- __*Annotation:*__ `@ORM/HasOne`.

	__Descrição:__ Define um relacionamento do tipo "um para um". É necessário que a classe de referência tem um atributo equivalente à outra ponta do relacionamento com a _annotation_ `@ORM/BelongsTo`.

	__Preenchimento:__ Opcional.

	__Propriedades:__

	- __Nome:__ `class`.

		__Descrição:__ Define qual classe deve ser referenciada no mapeamento.

		__Preenchimento:__ Obrigatório. O não preenchimento resulta em erro.

	- __Nome:__ `cascade`.

		__Descrição:__ Define que as operações de inserção, alteração e deleção pode acontecer em cascata, ou seja, a operação realizada na classe que mapeia essa _annotation_, deve ser estendida para a classe referenciada.

		__Valores:__ `INSERT`, `UPDATE`, `DELETE`, `ALL`.

		__Preenchimento:__ Opcional. Caso não seja preenchida, a operação não é estendida.

- __*Annotation:*__ `@ORM/HasMany`.

	__Descrição:__ Define um relacionamento do tipo "um para muitos". É necessário que a classe de referência tem um atributo equivalente à outra ponta do relacionamento com a _annotation_ `@ORM/BelongsTo`.

	__Preenchimento:__ Opcional.

	__Propriedades:__

	- __Nome:__ `class`.

		__Descrição:__ Define qual classe deve ser referenciada no mapeamento.

		__Preenchimento:__ Obrigatório. O não preenchimento resulta em erro.

	- __Nome:__ `cascade`.

		__Descrição:__ Define que as operações de inserção, alteração e deleção pode acontecer em cascata, ou seja, a operação realizada na classe que mapeia essa _annotation_, deve ser estendida para a classe referenciada.

		__Valores:__ `INSERT`, `UPDATE`, `DELETE`, `ALL`.

		__Preenchimento:__ Opcional. Caso não seja preenchida, a operação não é estendida.

- __*Annotation:*__ `@ORM/BelongsTo`.

	__Descrição:__ Define a outra ponta dos relacionamentos do tipo "um para um" e do tipo "um para muitos", ou seja, define a chave estrangeira para o relacionamento. É necessário que a classe de referência tem um atributo equivalente à outra ponta do relacionamento com a _annotation_ `@ORM/HasOne` ou `@ORM/HasMany`.

	__Preenchimento:__ Caso uma relação do tipo "um para um" ou do tipo "um para muitos" seja definida, é obrigatório que a classe referenciada possua essa _annotation_.

	__Propriedades:__

	- __Nome:__ `class`.

		__Descrição:__ Define qual classe deve ser referenciada no mapeamento.

		__Preenchimento:__ Obrigatório. O Não preenchimento resulta em erro.

	- __Nome:__ `cascade`.

		__Descrição:__ Define que as operações de inserção, alteração e deleção pode acontecer em cascata, ou seja, a operação realizada na classe que mapeia essa _annotation_, deve ser estendida para a classe referenciada.

		__Valores:__ `INSERT`, `UPDATE`, `DELETE`, `ALL`.

		__Preenchimento:__ Opcional. Caso não seja preenchida, a operação não é estendida.

	- __Nome:__ `optional`.

		__Descrição:__ Define se o relacionamento é opcional, ou seja, indica que o valor pode ou não ser nulo.

		__Preenchimento:__ Opcional. Valor padrão é "false".

- __*Annotation:*__ `@ORM/JoinColumn`.

	__Descrição:__ Define as informações da coluna que deve ser a chave estrangeira. Somente a propriedade que possui a _annotation_ `@ORM/BelongsTo` deve possuir essa _annotation_ para complementar as informações.

	__Preenchimento:__ Opcional. Assume os valores padrões das propriedades listadas a seguir.

	__Propriedades:__

	- __Nome:__ `name`.

		__Descrição:__ Define o nome da coluna a ser mapeada como chave estrangeira.

		__Preenchimento:__ Opcional. O valor padrão é o nome da propriedade mais o sufixo "_id", por exemplo, "pessoa_id".

- __*Annotation:*__ `@ORM/ManyToMany`.

	__Descrição:__ Define um relacionamento de do tipo "muitos para muitos". É necessário que a classe de referência tem um atributo equivalente à outra ponta do relacionamento com a _annotation_ `@ORM/ManyToMany`.

	__Preenchimento:__ Caso uma relação do tipo "muitos para muitos" seja definida, é obrigatório que a classe referenciada possua essa _annotation_.

	__Propriedades:__

	- __Nome:__ `class`.

		__Descrição:__ Define qual classe deve ser referenciada no mapeamento.

		__Preenchimento:__ Obrigatório. O não preenchimento resulta em erro.

	- __Nome:__ `cascade`.

		__Descrição:__ Define que as operações de inserção, alteração e deleção pode acontecer em cascata, ou seja, a operação realizada na classe que mapeia essa _annotation_, deve ser estendida para a classe referenciada.

		__Valores:__ `INSERT`, `UPDATE`, `DELETE`, `ALL`.

		__Preenchimento:__ Opcional. Caso não seja preenchida, a operação não é estendida.

	- __Nome:__ `mappedBy`.

		__Descrição:__ Define que o lado principal do mapeamento é a classe referenciada e define também qual é o atributo ao qual é o equivalente. O lado principal pode definir também as informações da tabela de ligação.

		__Preenchimento:__ Opcional.

- __*Annotation:*__ `@ORM/JoinTable`.

	__Descrição:__ Define informações para a tabela de ligação. Somente o lado principal do relacionamento deve possuir essa _annotation_ para complementar as informações.

	__Preenchimento:__ Opcional. Assume os valores padrões das propriedades listadas a seguir.

	__Propriedades:__

	- __Nome:__ `tableName`.

		__Descrição:__ Define o nome da tabela de ligação.

		__Preenchimento:__ Opcional. O valor padrão e composto pelo nome das duas tabelas que compõe o relacionamento, por exemplo, "empregado_role".

	- __Nome:__ `schema`.

		__Descrição:__ Define qual é o conjunto de tabelas ou banco de dados ao qual a tabela de ligação em questão existe.

		__Preenchimento:__ Opcional. Caso não preenchido, o _ORM_ assume que o schema a ser usado é o padrão definido na conexão ou nenhum, dependendo do banco de dados usado.

	- __Nome:__ `join`.

		__Descrição:__ Define o nome da coluna que é a chave estrangeira que aponta para o lado principal do relacionamento.

		__Preenchimento:__ Opcional. Assume os valores padrões das propriedades listadas a seguir.

		__Propriedades:__

		- __Nome:__ `name`.

			__Descrição:__ Define o nome da coluna a ser mapeada como chave estrangeira.

			__Preenchimento:__ Opcional. O valor padrão é o nome da propriedade mais o sufixo "_id", por exemplo, "empregado_id".

	- __Nome:__ `inverse`.

		__Descrição:__ Define o nome da coluna que é a chave estrangeira que aponta para o lado secundário do relacionamento.

		__Preenchimento:__ Opcional. Assume os valores padrões das propriedades listadas a seguir.

		__Propriedades:__

		- __Nome:__ `name`.

			__Descrição:__ Define o nome da coluna a ser mapeada como chave estrangeira.

			__Preenchimento:__ Opcional. O valor padrão é o nome da propriedade mais o sufixo "_id", por exemplo, "role_id".

[Voltar](#tabela-de-conteúdos)

### 3.2. Exemplo de Mapeamento Simples
------

Um exemplo básico de como criar uma classe do modelo, pode ser encontrado no exemplo a seguir. A classe "Empregado" mapeia a tabela "empregados" no banco de dados:

__Código Exemplo 11:__ Exemplo de classe do modelo mapeada

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=empregados)
 */
class Empregado {

    /**
     * @ORM/Id
     * @ORM/Generated
     * @ORM/Column(name=empregado_id, type=int)
     */
    public $id;

    /**
     * @ORM/Column(type=string, length=50)
     */
    public $nome;

    /**
     * @ORM/Column(name=data_nasc, type=date)
     */
    public $dataNasc;

}
```

O exemplo acima, de acordo com a __Código Exemplo 11__, exibe o mapeamento da classe "Empregado", ela representa a tabela "empregados" e possui os atributos `$id` (chave primária do tipo inteiro e com o nome da coluna "empregado_id"), `$nome` (tipo texto com o tamanho 50) e `$dataNasc` (tipo data com o nome da coluna "data_nasc").

[Voltar](#tabela-de-conteúdos)

### 3.3. Exemplos de Mapeamento de Relacionamentos
------

Relacionamento entre tabelas é um recurso essencial nos bancos de dados, para representar isso devidamente, no mundo orientado a objetos, existe as seguintes opções.

[Voltar](#tabela-de-conteúdos)

#### 3.3.1. Um para Um
------

Um exemplo básico de como mapear um relacionamento do tipo "um para um", pode ser encontrado no exemplo a seguir. A classe "Empregado" mapeia o relacionamento através do atributo `$informacoes`:

__Código Exemplo 12:__ Exemplo de relacionamento "um para um"

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=empregados)
 */
class Empregado {

    ...

    /**
     * @ORM/HasOne(class=App\Models\EmpregadoInfo, cascade={ALL})
     */
    public $informacao;

    ...

}
```

O exemplo acima, de acordo com a __Código Exemplo 12__, exibe o mapeamento da classe "Empregado", ela representa a tabela "empregados" e possui o atributo `$informacao`, que por sua vez, mapeia o relacionamento do tipo "um para um" com a classe "EmpregadoInfo".

A classe "EmpregadoInfo" referenciada pela classe "Empregado" seria:

__Código Exemplo 13:__ Exemplo de relacionamento "um para um" (outra ponta)

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 * @ORM/Table(name=empregado_info)
 */
class EmpregadoInfo {

    ...

    /**
     * @ORM/BelongsTo(class=App\Models\Empregado)
     * @ORM/JoinColumn(name=empregado_id)
     */
    public $empregado;

    ...

}
```

O exemplo acima, de acordo com a __Código Exemplo 13__, exibe o mapeamento da classe "EmpregadoInfo", ela representa a tabela "empregado_info" e possui o atributo `$empregado`, que por sua vez, mapeia a outra ponta o relacionamento do tipo "um para um" com a classe "Empregado" e mapeia a coluna "empregado_id" da tabela "pedido".

[Voltar](#tabela-de-conteúdos)

#### 3.3.2. Um para Muitos
------

Um exemplo básico de como mapear um relacionamento do tipo "um para muitos", pode ser encontrado no exemplo a seguir. A classe "Cliente" mapeia o relacionamento através do atributo `$pedidos`:

__Código Exemplo 14:__ Exemplo de relacionamento "um para muitos"

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Cliente {

    ...

    /**
     * @ORM/HasMany(class=App\Models\Pedido)
     */
    public $pedidos;

    ...

}
```

O exemplo acima, de acordo com a __Código Exemplo 14__, exibe o mapeamento da classe "Cliente", ela representa a tabela "cliente" e possui o atributo `$pedidos`, que por sua vez, mapeia o relacionamento do tipo "um para muitos" com a classe "Pedido".

A classe "Pedido" referenciada pela classe "Cliente" seria:

__Código Exemplo 15:__ Exemplo de relacionamento "um para muitos" (outra ponta)

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Pedido {

    ...

    /**
     * @ORM/BelongsTo(class=App\Models\Cliente)
     */
    public $cliente;

    ...

}
```

O exemplo acima, de acordo com a __Código Exemplo 15__, exibe o mapeamento da classe "Pedido", ela representa a tabela "pedido" e possui o atributo `$cliente`, que por sua vez, mapeia a outra ponta o relacionamento do tipo "um para muitos" com a classe "Cliente" e mapeia a coluna "cliente_id" da tabela "pedido".

[Voltar](#tabela-de-conteúdos)

#### 3.3.3. Muitos para Muitos
------

Um exemplo básico de como mapear um relacionamento do tipo "muitos para muitos", pode ser encontrado no exemplo a seguir. A classe "Empregado" mapeia o relacionamento através do atributo `$projetos`:

__Código Exemplo 16:__ Exemplo de relacionamento "muitos para muitos"

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Empregado {

    ...

    /**
     * @ORM/ManyToMany(class=App\Models\Projeto)
     * @ORM/JoinTable(tableName=empregado_projeto, join={ name=empregado_id }, join={ name=projeto_id })
     */
    public $projetos;

    ...

}
```

O exemplo acima, de acordo com a __Código Exemplo 16__, exibe o mapeamento da classe "Empregado", ela representa a tabela "empregado" e possui o atributo `$projetos`, que por sua vez, mapeia o relacionamento do tipo "muitos para muitos" com a classe "Projeto" e é o lado principal do relacionamento, isso quer dizer que é o lado do relacionamento que define as informações da tabela de ligação.

A classe "Projeto" referenciada pela classe "Empregado" seria:

__Código Exemplo 17:__ Exemplo de relacionamento "muitos para muitos" (outra ponta)

```php
<?php

namespace App\Models;

/**
 * @ORM/Entity
 */
class Projeto {

    ...

    /**
     * @ORM/ManyToMany(class=App\Models\Empregado, mappedBy=projetos)
     */
    public $empregados;

    ...

}
```

O exemplo acima, de acordo com a __Código Exemplo 17__, exibe o mapeamento da classe "Projeto", ela representa a tabela "projeto" e possui o atributo `$empregados`, que por sua vez, mapeia a outra ponta o relacionamento do tipo "muitos para muitos" com a classe "Empregado" e indica que a tabela de ligação é mapeada pelo atributo `$projetos`.

[Voltar](#tabela-de-conteúdos)

## 4. _ENTITY MANAGER_

O _EntityManager_ é o gerenciador de entidades do _ORM_, através dele é que o desenvolvedor tem acesso às funções de consulta, persistência e deleção.

Essa seção irá abordar as funcionalidades que envolvem o _EntityManager_.

[Voltar](#tabela-de-conteúdos)

### 4.1. Obtendo Uma Instância do _EntityManager_
------

Para obter uma instância, basta que o _ORM_ crie uma, da seguinte maneira:
 
__Código Exemplo 18:__ Exemplo de como obter uma instância do _EntityManager_

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $orm->addConnection('exemplo-sqlite');
    $em = $orm->createEntityManager();
?>
```

O método _createEntityManager_ retorna uma instância do _EntityManager_ utilizando a conexão padrão do _ORM_, ou seja, todas as operações realizadas no _EntityManager_ serão realizadas através da conexão padrão, no caso do exemplo acima, de acordo com a __Código Exemplo 18__, a conexão "exemplo-mysql".

Para utilizar uma conexão secundária, é necessário passar o nome da conexão para o método createEntityManager:

__Código Exemplo 19:__ Exemplo de como obter uma instância do _EntityManager_ com conexão segundária

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $orm->addConnection('exemplo-sqlite');
    $em = $orm->createEntityManager('exemplo-sqlite');
?>
```

Agora o método _createEntityManager_ irá retorna uma instância do _EntityManager_ utilizando a conexão secundária "exemplo-sqlite".

[Voltar](#tabela-de-conteúdos)

### 4.2. Transações
------

A transação no banco de dados, é uma unidade que realiza um trabalho, ou seja, qualquer trabalho realizado no banco de dados, mesmo que em etapas, é realizado dentro de uma transação e todas as operações realizadas dentro dessa transação tem a garantia de ser executada integralmente no banco de dados, isso significa que caso um problema ocorra durante a execução de uma transação, as operações já realizadas dentro da mesma transação serão desfeitas.

Bernstein (2009) define que uma transação de banco de dados deve ser atômica, consistente, isolada e durável conhecido pela sigla ACID:

- Atômica: uma série indivisível e irredutível de operações de banco de dados;
- Consistente: toda e qualquer transação deve alterar os dados no banco apenas de formas permitidas, ou seja, quaisquer dados gravados devem ser válidos de acordo com todas as regras definidas na tabela;
- Isolada: determina como a integridade da transação é visível para outros usuários e sistemas;
- Durável: garante que as transações que foram confirmadas sobreviverão permanentemente no banco de dados.

[Voltar](#tabela-de-conteúdos)

#### 4.2.1. _BeginTransaction_
------

Para iniciar uma transação no _ORM_, o desenvolvedor deve usar o método _beginTransaction_.

__Código Exemplo 20:__ Exemplo de como iniciar uma trasação

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $em->beginTransaction();
?>
```

O método _beginTransaction_ irá criar iniciar uma transação para a conexão padrão do _ORM_. Para fechar a transação, o desenvolvedor deverá executar o método _commit_ ou o método _rollback_.

[Voltar](#tabela-de-conteúdos)

#### 4.2.2. _Commit_
------

O método _commit_ irá confirmar a transação atual para que as operações realizadas no banco de dados sejam efetivadas. Liberando assim o _EntityManager_ para criar uma nova transação.

__Código Exemplo 21:__ Exemplo de como aplicar alterações de uma trasação

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $em->beginTransaction();
    // operações no banco de dados, por exemplo, um insert
    $em->commit();
?>
```

[Voltar](#tabela-de-conteúdos)

#### 4.2.3. _Rollback_
------

O método _rollback_ irá desfazer as operações realizadas na transação atual, liberando assim o _EntityManager_ para criar uma nova transação. Esse método é normalmente utilizado em um `try/catch` para tratamento de erros.

__Código Exemplo 22:__ Exemplo de como desfazer alterações de uma trasação

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    try {
        $em->beginTransaction();
        // operações no banco de dados
        $em->commit();
    } catch(Exception $ex) {
        $em->rollback();
    }
?>
```

[Voltar](#tabela-de-conteúdos)

### 4.3. _Find_
------

O método _find_ é utilizado para carregar um registro do banco de dados através da chave primária dessa tabela.

__Código Exemplo 23:__ Exemplo de como carregar registro por chave primária

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $id = 1;
    $empr = $em->find(App\Models\Empregado::class, $id);
?>
```

O método _find_ espera como parâmetro a classe que mapeia a tabela a ser utilizada na consulta e a chave primária correspondente ao registro necessário.

[Voltar](#tabela-de-conteúdos)

### 4.4. _List_
------

O método _list_ é utilizado para carregar uma lista de registros do banco de dados, porém, sem utilizar filtros (clausula _where_).

__Código Exemplo 24:__ Exemplo de como listar os registro de uma dada tabela

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    $emprs = $em->list(Empregado::class);
    $emprs = $em->list(Empregado::class, 15);
    $emprs = $em->list(Empregado::class, 2, 10);
?>
```

O método _list_ pode ser usado de três formas. A primeira, passando como parâmetro somente a classe que mapeia a tabela a ser listada, e traz todos os registros que estão atualmente na tabela. A segunda, passando como parâmetro a classe que mapeia a tabela a ser lista e a quantidade de registros a serem carregados pela consulta, no caso do exemplo, os primeiros 15 registros. A terceira e última forma, utilizada para dividir os registros em páginas, passando como parâmetro a classe que mapeia a tabela a ser lista, a página a ser trazida e a quantidade de registros que a página deve conter.

[Voltar](#tabela-de-conteúdos)

### 4.5. _QueryBuilder_
------

Para criar consultas, desde as simples até as complexas, o desenvolvedor pode optar por usar o _QueryBuilder_.

[Voltar](#tabela-de-conteúdos)

#### 4.5.1. Obtendo o _QueryBuilder_
------

Para obter uma instância, basta que o _EntityManager_ crie uma, da seguinte maneira:

__Código Exemplo 25:__ Exemplo de como obter uma instância do _QueryBuilder_

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $q1 = $orm->createQuery();
    $q2 = $orm->createQuery(App\Models\Empregado::class,'u');
?>
```

O método _createQuery_ retorna uma instância do _QueryBuilder_ e pode ser utilizado de duas formas, na primeira, basta chamar o método sem passar parâmetro algum e ele irá retornar uma instância sem informação alguma, da segunda maneira, o desenvolvedor pode passar a classe que mapeia a tabela a ser consultada e um _alias_ para a tabela. O _alias_ é um apelido que pode ser atribuído para uma tabela em uma consulta, é normalmente utilizado com reduzir o nome de tabelas e para realizar consultas com `Self Join` (consultas que fazem ligação de uma tabela com ela mesma).

[Voltar](#tabela-de-conteúdos)

#### 4.5.2. Criando uma consulta simples usando _list_
------

Para realizar uma consulta simples, o desenvolvedor pode fazer da seguinte maneira:

__Código Exemplo 26:__ Exemplo de como listar os registro de uma dada tabela usando o _QueryBuilder_

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery();
    $query->from(Empregado::class);
    $emprs = $query->list();
?>
```

Ou:

__Código Exemplo 27:__ Exemplo de como listar os registro de uma dada tabela usando o _QueryBuilder_

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery(Empregado::class);
    $emprs = $query->list();
?>
```

Ou ainda:

__Código Exemplo 28:__ Exemplo de como listar os registro de uma dada tabela usando o _QueryBuilder_

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $emprs=$orm->createQuery(Empregado::class)->list();
?>
```

A consulta ilustrada acima, de acordo com a __Código Exemplo 28__, irá retornar todos os registros da tabela mapeada pela classe "Empregado" em um _array_ com instâncias da mesma classe, onde cada instância representa um registro da tabela.

Para tabelas que possuem um número muito grande de registros, recomenda-se paginar ou filtrar os registros.

[Voltar](#tabela-de-conteúdos)

#### 4.5.3. Criando uma consulta simples usando _one_
------

Além do método _list_ o _QueryBuilder_ possui o método _one_, que nesse caso, retorna apenas um registro mapeado em uma instância da classe indicada no método from. Por exemplo:

__Código Exemplo 29:__ Exemplo de como carregar um registro de uma dada tabela usando o _QueryBuilder_

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $empr = $orm->createQuery(Empregado::class)->one();
?>
```

A consulta acima, de acordo com a __Código Exemplo 29__, irá retornar um registro da tabela mapeada pela classe "Empregado", apenas o primeiro registro retornado pela consulta.

[Voltar](#tabela-de-conteúdos)

#### 4.5.4. _Join_
------

Para realizar uma consulta com múltiplas tabelas, o desenvolvedor pode fazer da seguinte maneira:

__Código Exemplo 30:__ Exemplo de como realizar uma consulta com múltiplas tabelas

```php
<?php
    use App\Models\Empregado;
    use App\Models\Projeto;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery();
    $query->from(Empregado::class, 'e');
    $query->join(Projeto::class, 'p');
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 30__, será executada com as tabelas mapeadas pelas classes "Empregado" e "Projeto". O resultado será todos os registros da tabela mapeada pela classe "Empregado" (informado no método _from_) em um _array_ com instâncias da mesma classe, onde cada instância representa um registro da tabela.

O método _join_ pode ser chamado quantas vezes forem necessárias, porém, o desenvolvedor deve ter em mente que, quanto mais tabelas forem adicionadas à consulta, mais a consulta ficará pesada para ser executada.

Para fazer uma consulta usando o chamado `Outer Join`, o desenvolvedor pode passar como terceiro parâmetro o tipo de _join_ (`left join` ou `right join`) que deve ser usado, por exemplo, para realizar um `left join`:
 
__Código Exemplo 31:__ Exemplo de como realizar uma consulta com múltiplas tabelas usando `OUTER JOIN`

```php
<?php
    use App\Models\Empregado;
    use App\Models\Projeto;
    use ORM\Contants\JoinTypes;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery();
    $query->from(Empregado::class, 'e');
    $query->join(Projeto::class, 'p', JoinTypes::LEFT);

    $emprs = $query->list();
?>
```

Caso o tipo de `join` não for informado, o valor padrão assumido é `INNER`.

Observação: o método _join_ não implica que a tabela informada como parâmetro nesse método será carregada juntamente com a tabela informada como parâmetro no método _from_. Essa ação é conhecida como `Eager Load`, porém o _ORM_ atualmente suporta apenas `Lazy Load`, ou seja, os registros dos relacionamentos serão carregados somente quando necessário (sob demanda).

[Voltar](#tabela-de-conteúdos)

#### 4.5.5. _Where_
------

Para realizar uma consulta com utilizando filtro, o desenvolvedor pode fazer da seguinte maneira:
 
__Código Exemplo 32:__ Exemplo de como realizar consultas com filtros

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery(Empregado::class, 'e');
    $query->where('e.nome')->equals('João da Silva');
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 32__, irá retornar um _array_ contendo todos os registros da tabela mapeada pela classe "Empregado" cuja coluna "nome" possua o valor "João da Silva".

Utilizar mais de um filtro, pode ser feito através do método _and_ ou _or_:

__Código Exemplo 33:__ Exemplo de como realizar consultas com filtros

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery(Empregado::class, 'e');
    $query->where('e.nome')->equals('João da Silva');
    $query->or('e.nome')->equals('Predo da Silva');
    $query->and('e.dataNasc')->equals(new DateTime('1990-05-23'));
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 33__, irá retornar um _array_ contendo todos os registros da tabela mapeada pela classe "Empregado" cuja coluna "nome" possua o valor "João da Silva" ou o valor "Pedro da Silva" e a coluna "dataNasc" seja igual à data "23/05/2018".

Para realizar uma consulta com filtro e múltiplas tabelas, o desenvolvedor pode fazer da seguinte maneira:

__Código Exemplo 34:__ Exemplo de como realizar consultas com filtros e multiplas tabelas

```php
<?php
    use App\Models\Empregado;
    use App\Models\Projeto;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $orm->createQuery();
    $query->from(Empregado::class, 'e');
    $query->join(Projeto::class, 'p');
    $query->where('e.nome')->equals('João da Silva');
    $query->and('p.nome')->equals('PRJ_OZOB');
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 34__, irá retornar um _array_ contendo todos os registros da tabela mapeada pela classe "Empregado" cuja coluna "nome" possua o valor "João da Silva" e a coluna "nome" da tabela mapeada pela classe "Projeto" possua o valor "PRJ_OZOB".

Os métodos _where_, and e or esperam receber por parâmetro um texto que indica o _alias_ utilizado pela classe e qual atributo da classe deve ser utilizado para o filtro. O retorno desses métodos é um objeto que contém todas as operações de comparação que podem ser utilizados no filtro.
 
As operações permitidas são:

| Operação | Parâmetros | Atalho | Descrição |
|----------|------------|--------|-----------|
| equals | `$valor` | eq | Compara se os valores são iguais |
| notEquals | `$valor` | neq | Compara se os valores são diferentes |
| isNull | - | isn | Compara se o valor da coluna é nulo |
| isNotNull | - | isnn | Compara se o valor da coluna não é nulo |
| between | `$inicial`, `$final` | bt | Compara se o valor da coluna está entre o valor `$inicial` e o valor `$final` |
| notBetween | `$inicial`, `$final` | nbt | Compara se o valor da coluna não está entre o valor `$inicial` e o valor `$final` |
| greaterThan | `$valor` | gt | Compara se o valor da coluna é maior do que o `$valor` |
| greaterOrEqualsThan | `$valor` | goet | Compara se o valor da coluna é maior ou igual ao `$valor` |
| lessThan | `$valor` | lt | Compara se o valor da coluna é menor do que o `$valor` |
| lessOrEqualsThan | `$valor` | loet | Compara se o valor da coluna é menor ou igual ao `$valor` |
| in | _Array_ `$valores` | - | Compara se o valor da coluna está dentro dos `$valores` do _array_ |
| notIn | _Array_ `$valores` | - | Compara se o valor da coluna não está dentro dos `$valores` do _array_ |
| like | `$valor` | lk | Compara o valor da coluna é compatível com `$valor` informado. O valor informado pode utilizar o "%" como coringa em qualquer posição, por exemplo, no começo do `$valor` "%da Silva" indica que o valor da coluna deve terminar com o trecho "da Silva" |
| notLike | `$valor` | nlk | Compara o valor da coluna não é compatível com `$valor` informado. O valor informado pode utilizar o "%" como coringa em qualquer posição, por exemplo, no começo do `$valor` "%da Silva" indica que o valor da coluna deve terminar com o trecho "da Silva" |
| contains | `$valor` | ctn | Compara se o `$valor` está contido no valor da coluna. |
| notContains | `$valor` | nctn | Compara se o `$valor` não está contido no valor da coluna. |
| beginsWith | `$valor` | bwt | Compara se o valor da coluna começa com `$valor`. |
| notBeginsWith | `$valor` | nbwt | Compara se o valor da coluna não começa com `$valor`. |
| endsWith | `$valor` | ewt | Compara se o valor da coluna termina com `$valor`. |
| notEndsWith | `$valor` | newt | Compara se o valor da coluna não termina com `$valor`. |

[Voltar](#tabela-de-conteúdos)

#### 4.5.6. Paginação
------

A paginação é um recurso muito útil para o desenvolvedor, ele permite que os registros sejam divididos em páginas menores para serem listados em tela para o usuário, caso contrário, todos os registros que existem na tabela seriam exibidos de uma só vez.

Isso não parece assim tão mal, porém, em um caso onde a tabela possua milhares ou mesmo centenas de milhares de registros, levaria muito tempo para carregar tudo, ou o sistema iria travar, ou ainda resultaria em erro.

A paginação serve para resolver esse tipo de problema, e pode ser utilizada das seguintes maneiras:

__Código Exemplo 35:__ Exemplo de como realizar consultas com paginação

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    $emprs = $em->list(Empregado::class, 2, 10);
?>
```

__Código Exemplo 36:__ Exemplo de como realizar consultas com paginação usando QueryBuilder

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $page = 2;
    $quantity = 10;
    $query = $em->createQuery(Empregado::class);
    $query->page($page, $quantity);
    $emprs = $query->list();
?>
```

Ambas as consultas realizam uma consulta na tabela mapeada pela classe "Empregado". O _ORM_ irá gerar uma consulta onde ele divide os registros da tabela em páginas, contendo 10 registros por página, então irá retornar um _array_ contendo os registros da segunda página.

Uma maneira alternativa para recuperar a primeira página seria o método top:

__Código Exemplo 37:__ Exemplo de como realizar consultas com paginação usando top

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $em->createQuery(Empregado::class);
    $query->top(10);
    $emprs = $query->list();
?>
```

As queries acima, de acordo com a __Código Exemplo 37__, realizam uma consulta na tabela mapeada pela classe "Empregado" muito semelhante às anteriores. O _ORM_ irá gerar uma consulta onde ele irá retornar um _array_ contendo 10 registros, que seria equivalente à primeira página.

[Voltar](#tabela-de-conteúdos)

#### 4.5.7. _OrderBy_
------

O método _orderBy_ define a ordem em que os registros devem ser retornados na consulta.

__Código Exemplo 38:__ Exemplo de como realizar consultas ordenadas

```php
<?php
    use App\Models\Empregado;
    use ORM\Constants\OrderTypes;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();
    $query = $em->createQuery(Empregado::class);
    $query->orderBy('e.dataNasc', OrderTypes::DESC);
    $query->orderBy('e.nome', OrderTypes::ASC);
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 38__, irá retornar todos os registros da tabela mapeada pela classe "Empregado" em um _array_ com instâncias da mesma classe, onde cada instância representa um registro da tabela, ordenados pelas colunas "dataNasc" em ordem decrescente e "nome" em ordem crescente.

O método _orderBy_ é acumulativo, pode ser chamado quantas vezes forem necessárias. Caso o segundo parâmetro não for informado, o _ORM_ irá usar a ordem padrão que é `ASC` (ascendente).

[Voltar](#tabela-de-conteúdos)

#### 4.5.8. _GroupBy_
------

O método _groupBy_ define um agrupamento para criar relatórios e totalizações separados em grupos. A instrução de agrupamento é frequentemente usada com funções de agregação para agrupar o conjunto de resultados em uma ou mais colunas.
 
__Código Exemplo 39:__ Exemplo de como realizar consultas agrupadas

```php
<?php
    use App\Models\Empregado;
    use ORM\Constants\OrderTypes;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    $query = $em->createQuery(Empregado::class);
    $query->count('e.id', 'total');
    $query->groupBy('e.dataNasc');
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 39__, irá retornar em um _array_ contendo o resultado da consulta, onde cada entrada do resultado é um _array_ que possui os campos "total" e "dataNasc". O valor de "total" representa uma contagem de quantos "Empregados" nasceram no mesmo dia representado pelo valor de "dataNasc".

O método _groupBy_ pode receber por parâmetro quantos parâmetros forem necessários, um após o outro, na mesma chamada do método.

[Voltar](#tabela-de-conteúdos)

#### 4.5.9. Agregação
------

Uma função de agregação executa um cálculo em um conjunto de valores e retorna um único valor. As funções de agregação frequentemente são usadas com a cláusula `GROUP BY` em uma consulta.
 
__Código Exemplo 40:__ Exemplo de como realizar consultas com funcões de agregação

```php
<?php
    use App\Models\Empregado;
    use ORM\Constants\OrderTypes;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    $query = $em->createQuery(Empregado::class);
    $query->count('e.id', 'total');
    $query->max('e.salario', 'maiorSalario');
    $query->groupBy('e.dataNasc');

    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 40__, irá retornar em um _array_ contendo o resultado da consulta, onde cada entrada do resultado é um _array_ que possui os campos "total", "maiorSalario" e "dataNasc". O valor de "total" representa uma contagem de quantos "Empregados" nasceram no mesmo dia representado pelo valor de "dataNasc", e o valor de "maiorSalario" representa o maior salário que um "Empregado", que nasceu nesse dia, recebe.

O segundo parâmetro do método de agregação, por exemplo, o `total`, é um _alias_ que o desenvolvedor pode atribuir para o campo para facilitar na utilização do mesmo.

As funções de agregação permitidas são:

| Operação | Parâmetros | Descrição |
|----------|------------|-----------|
| avg | `$coluna`, `$alias` | Calcula a média dos valores da `$coluna` |
| sum | `$coluna`, `$alias` | Calcula a soma dos valores da `$coluna` |
| min | `$coluna`, `$alias` | Retorna o menor valor da `$coluna` |
| max | `$coluna`, `$alias` | Retorna o maior valor da `$coluna` |
| count | `$coluna`, `$alias` | Calcula a quantidade dos valores da `$coluna` |

[Voltar](#tabela-de-conteúdos)

#### 4.5.10. _Having_
------

O método _having_ especifica um critério de filtro utilizando uma função de agregação, pois a clausula `where` não consegue realizar esse tipo de filtro. Na maioria dos bancos de dados, é obrigatório usado com a cláusula `GROUP BY` antes de usar o `having`.

__Código Exemplo 41:__ Exemplo de como realizar consultas com `having`

```php
<?php
    use App\Models\Empregado;

    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    $query = $em->createQuery(Empregado::class);
    $query->groupBy('e.dataNasc');
    $query->having()
        ->avg('e.salario')->gt(5000)->and()
        ->count('e.id')->gt(5);
    $emprs = $query->list();
?>
```

A consulta acima, de acordo com a __Código Exemplo 41__, irá retornar uma lista contendo a "dataNasc" da tabela mapeada pela classe "Empregado", filtrando pelos registros cujo salário seja maior do que a média, e que a quantidade de registros agrupados pela data seja maior do que 5.

As funções de agregação são as mesmas das descritas na seção [_4.5.10 Having_](#4510-having) e os métodos de comparação são os mesmos dos descritos na seção [_4.5.5 Where_](#455-where) com exceção dos métodos: _in_, _notIn_, _like_, _notLike_, _contains_, _notContains_, _beginsWith_,_ notBeginsWith_, _endsWith_, _notEndsWith_.

[Voltar](#tabela-de-conteúdos)

### 4.6. _Save_
------

O método _save_ é utilizado para cadastrar ou alterar um registro no banco de dados. Caso o registro já exista e a chave primária esteja preenchida, o método _save_ irá atualizar o registro existente no banco de dados, caso contrário, um novo registro será adicionado ao banco.

__Código Exemplo 42:__ Exemplo de como utilizar o método save para incluir registro

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    try {
        $empregado = new App\Models\Empregado();
        $empregado->nome = 'João da Silva';
        $empregado->dataNasc = new DateTime('1990-05-23');

        $em->beginTransaction();
        $empregadoNovo = $em->save($empregado);
        $em->commit();
    } catch(Exception $ex) {
        $em->rollback();
    }
?>
```

O exemplo acima, de acordo com a __Código Exemplo 42__, irá resultar em um novo registro na tabela "empregado". O método _save_ irá retornar o novo registro.

__Código Exemplo 43:__ Exemplo de como utilizar o método _save_ para alterar registro

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    try {
        $empr = $em->find(App\Models\Empregado::class, 1);
        $empr->nome = 'Pedro da Silva';

        $em->beginTransaction();
        $emprAtualizado = $em->save($empr);
        $em->commit();
    } catch(Exception $ex) {
        $em->rollback();
    }
?>
```

O exemplo acima, de acordo com a __Código Exemplo 43__, irá atualizar um registro já existente na tabela "empregado" com a chave primária de valor "1". O método _save_ irá retornar o registro atualizado.

[Voltar](#tabela-de-conteúdos)

### 4.7. _Remove_
------

O método _remove_ é utilizado para deletar um registro no banco de dados, caso o mesmo exista.

__Código Exemplo 44:__ Exemplo de como utilizar o método _remove_ para deletar registro

```php
<?php
    $orm = ORM\Orm::getInstance();
    $orm->setConnection('exemplo-mysql');
    $em = $orm->createEntityManager();

    try {
        $empr = $em->find(App\Models\Empregado::class, 1);

        $em->beginTransaction();
        $linhas = $em->remove($empr);
        $em->commit();
    } catch(Exception $ex) {
        $em->rollback();
    }
?>
```

O exemplo acima, de acordo com a __Código Exemplo 44__, irá remover um registro já existente na tabela "empregado" com a chave primária de valor "1". O método _remove_ irá devolver a quantidade de linhas afetadas com essa operação.

[Voltar](#tabela-de-conteúdos)

## 5. _LOGGER_

É possível definir um `log` para que o _ORM_ possa registrar as operações que ele realiza. Através de alguns métodos o desenvolvedor pode configurar o _Logger_ para as suas necessidades.

O método `setLogDisable` define se o `log` deve estar ativo ou inativo. Passando `false` por parâmetro para essa função, o desenvolvedor desabilita o _Logger_ e passando `true` ele é habilitado novamente.

O método `setLogLocation` define a localização onde os arquivos do `log` serão salvos.

O método `setLogFilename` define o prefixo que será utilizado para criar o nome do arquivo de `log`.

O método `setLogLevel` define qual o nível de `log` o _ORM_ deve registrar, os valores esperados são `DEBUG`, `INFO`, `WARNING` ou `ERROR`. O valor padrão é `ERROR`.

O método `setLogOccurrency` define com qual frequência o _Logger_ deve criar um novo arquivo. Essa configuração impede que os arquivos de `log` tenham tamanhos grandes demais. Os valores esperados são `DAILY` ou `MONTHLY`. O valor padrão é `DAILY`.

O método `getLogger` retorna a instância que está sendo utilizada pelo _ORM_. O _Logger_ do _ORM_ pode ser utilizado em outras partes da aplicação. Para isso basta usar os métodos _debug_, _info_, _warning_ e _error_ para registrar uma entrada no `log` conforme o nível desejado.

[Voltar](#tabela-de-conteúdos)

## 6. _DRIVER_

O desenvolvedor pode criar o próprio _Driver_ para um banco de dados ou uma versão ainda não suportada pelo _ORM_. O _Driver_ permite que o _ORM_ consiga se comunicar com o banco de dados correspondente, por exemplo, o banco de dados _MySQL_, possui um _Driver_ correspondente para permitir a comunicação do _ORM_ com este banco de dados.

Para criar um novo _Driver_, o desenvolvedor precisa atender a algumas regras:

- __Nome do arquivo:__ o nome do arquivo precisa conter o nome do banco de dados de acordo com o nome esperado pelo `PDO` (_PHP Data Object_, é uma interface do _PHP_ para acessar banco de dados), por exemplo, `sqlite.php`. Caso o _Driver_ seja para uma vesão específica, a versão precisa estar também no nome do arquivo, por exemplo, para a versão 5.11 do banco de dados _MySQL_, o nome do arquivo seria `mysql-5.11.php`;
- __Nome da classe:__ o nome da classe precisa ser equivalente ao nome do arquivo conforme definido acima, por exemplo, para a versão 5.11 do banco de dados _MySQL_, o nome da classe seria `MySQLDriver_5_11`. E caso não possua uma versão definida, `MySQLDriver`. Essa é mais uma sugestão do que uma regra, mas ajuda evitar problemas de classe com nomes iguais;
- __Retonar o nome da classe:__ o nome da classe precisa ser retornado ao final do arquivo, por exemplo, `return MySQLDriver::class;` ;
- __Localização do arquivo:__ o arquivo precisa estar localizado na pasta `driver` dentro da pasta raiz do _ORM_, por exemplo, `/home/user/app/orm/driver`;
- __Precisa estender de `ORM\Core\Driver`:__ a classe para o novo _Driver_ precisa extender a classe `ORM\Core\Driver`, do contrário, o _ORM_ não irá considerar como válido.
- __O novo _Driver_ precisa ser _Singleton_:__ a classe precisa seguir o _Desing Pattern Singleton_ para que o _ORM_ use uma instância única do _ORM_. Caso a classe não seja _Singleton_, o _ORM_ irá emitir um erro.
- __As configurações do novo _Driver_:__ o _Driver_ possui uma série de configurações para que o _ORM_ consiga se comunicar com o banco de dados. Elas devem ser feitas no método construtor da classe. As configurações serão listadas mais à frente.

O _Driver_ possui as seguintes configurações:

- __$GENERATE_ID_TYPE:__ indica como o banco de dados faz a incrementação do valor da chave primária. Os valores esperados são: `ATTR`, `QUERY` ou `SEQUENCE`;
- __$GENERATE_ID_ATTR:__ caso o tipo de incrementação seja igual a `ATTR`, aqui é definida qual é o atributo que o _ORM_ irá usar, por exemplo, `AUTO_INCREMENT` para o banco de dados _MySQL_;
- __$GENERATE_ID_QUERY:__ caso o tipo de incrementação seja igual a `QUERY` ou `SEQUENCE`, aqui é definida query que o _ORM_ irá usar para realizar a incrementação do valor da chave primária, por exemplo, `select orm_sequence.nextval from dual` para o banco de dados Oracle;
- __$SEQUENCE_NAME:__ define o nome da sequence que o _ORM_ irá criar caso o _ORM_ precise criar as tabelas no banco de dados. O valor padrão é `orm_sequence`;
- __$IGNORE_ID_DATA_TYPE:__ caso o valor desse atributo seja `true`, o _ORM_ irá ignorar o valor definido para a coluna, como pode acontecer, por exemplo, com o banco de dados _PostgreSQL_;
- __$FK_ENABLE:__ indica de o _ORM_ deve criar o campo como chave estrangeira. O valor padrão é `true`;
- __$PAGE_TEMPLATE:__ define como o banco de dados faz uma consulta paginada, por exemplo, o banco de dados _MySQL_ possui a clausula `LIMIT` para realiza essa tarefa;
- __$TOP_TEMPLATE:__ define como o banco de dados faz uma consulta trazendo somente um determinado número de registros, por exemplo, o banco de dados _MySQL_ possui a clausula `LIMIT` para realiza essa tarefa;
- __$DATA_TYPES:__ define os tipos de dados aceitos pelo _ORM_ e mapeia para os dados suportados pelo banco de dados. Os tipos recomendados são: `string`, `int`, `float`, `lob` (_Large Object_, por exemplo, `BLOB` ou `CLOB`), `date`, `time`, `datetime`, `bool`;
- __$FORMATS:__ define os formatos que o banco de dados aceita para os tipos `date`, `time` e `datetime`.

A seguir, um exemplo de implementação para uma classe do `Driver`:

__Código Exemplo 45:__ Exemplo de _Driver_ para o banco de dados _MySQL_

```php
<?php

use ORM\Contants\GeneratedTypes;
use ORM\Core\Driver;

if (!class_exists('MySQLDriver_5_11')) {

    class MySQLDriver_5_11
    {

        private static $instance;

        const NAME = 'MySQL';

        const VERSION = '5.11';

        private function __construct()
        {
            $this->GENERATE_ID_TYPE = GeneratedTypes::ATTR;
            $this->GENERATE_ID_ATTR = 'AUTO_INCREMENT';
            $this->PAGE_TEMPLATE = "%s \nLIMIT %d, %d";
            $this->TOP_TEMPLATE = "%s \nLIMIT %d";
            $this->DATA_TYPES = [
                'string' => 'VARCHAR(%d)',
                'int' => 'INTEGER',
                'float' => 'DOUBLE',
                'lob' => 'TEXT',
                'date' => 'DATE',
                'time' => 'TIME',
                'datetime' => 'DATETIME',
                'bool' => 'TINYINT(1)',
            ];
        }

        public static function getInstance() : Driver
        {
            if (!self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function getConnection(Array $config) : \PDO
        {
            $this->validateFields(['db', 'host', 'schema', 'user', 'pass'], $config);
            $dsn = "$config[db]:host=$config[host];dbname=$config[schema]";

            return $this->createConnection($dsn, $config['user'] ?? null, $config['pass'] ?? null);
        }

    }

}

return MySQLDriver_5_11::class;
```

[Voltar](#tabela-de-conteúdos)
