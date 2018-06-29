# Lamberjack's ORM

## Intodução

Lamberjack"s ORM é um framework ORM para a linguagem PHP. ORM é uma sigla em inglês que significa Object-Relational Mapper. Um ORM é uma ferramenta bastante útil no dia-a-dia do desenvolvedor de software.

O Lamberjack"s ORM trabalha com mapeamento de tabelas em classes do modelo de dados utilizando Annotations. O ORM permite ao desenvolvedor trabalhar com mais de uma conexão de banco de dados e criar as tabelas do banco de dados baseado nas classes do modelo.

## Tabela de Conteúdos

1. [INSTALAÇÃO](#instalação)
1. [DEFININDO CONEXÕES](#definindo-conexões)
	1. [Criar Tabelas Automaticamente](#criar-tabelas-automaticamente)
1. [DEFININDO MODELOS](#definindo-modelos)
	1. [_Annotations_](#annotations)
		1. [Annotations de Classes](#annotations-de-classes)
		1. [Annotations de Propriedades](#annotations-de-propriedades)
		1. [Annotations de Relacionamentos](#annotations-de-relacionamentos)
	1. [Exemplo de Mapeamento Simples](#exemplo-de-mapeamento-simples)
	1. [Exemplos de Mapeamento de Relacionamentos](#exemplos-de-mapeamento-de-relacionamentos)
		1. [Um para Um](#um-para-um)
		1. [Um para Muitos](#um-para-muitos)
		1. [Muitos para Muitos](#muitos-para-muitos)
1. [_ENTITY MANAGER_](#entity-manager)
	1. [Obtendo Uma Instância do _EntityManager_](#obtendo-uma-instância-do-entitymanager)
	1. [Transações](#transações)
		1. [_BeginTransaction_](#begintransaction)
		1. [_Commit_](#commit)
		1. [_Rollback_](#rollback)
	1. [_Find_](#find)
	1. [_List_](#list)
	1. [_QueryBuilder_](#querybuilder)
		1. [Obtendo o _QueryBuilder_](#obtendo-o-querybuilder)
		1. [Criando uma consulta simples usando _list_](#criando-uma-consulta-simples-usando-list)
		1. [Criando uma consulta simples usando _one_](#criando-uma-consulta-simples-usando-one)
		1. [_Join_](#join)
		1. [_Where_](#where)
		1. [Paginação](#paginação)
		1. [_OrderBy_](#orderby)
		1. [_GroupBy_](#groupby)
		1. [Agregação](#agregação)
		1. [_Having_](#having)
	1. [_Save_](#save)
	1. [_Remove_](#remove)
1. [_LOGGER_](#logger)
1. [_DRIVER_](#driver)

## 1. INSTALAÇÃO
---
Para usar o Lamberjack"s ORM, pode-se obtê-lo no repositório orm no GitHub no link: https://github.com/dfrancklin/orm.
Basta então copiar a pasta `/orm` que foi baixada e para incluir o ORM no projeto, use o comando `require_once`:

Figura 1: Incluir o ORM ao projeto
```php
<?php
	require_once "./orm/load.php";
?>
```
Fonte: Autor, 2018

[Voltar](#tabela-de-conteúdo)

## 2. DEFININDO CONEXÕES
---
As conexões que serão utilizadas pelo ORM devem ser declaradas em um arquivo com extensão `.php`. Por padrão, o arquivo é esperado que esteja na pasta raiz do ORM com o nome `connection.config.php`, ou seja, supondo que o ORM esteja localizado `/home/user/app/orm/`, então o caminho para o arquivo seria `/home/user/app/orm/connection.config.php`.

O arquivo de conexões pode ser substituído da seguinte maneira:

Figura 2: Definindo qual arquivo de conexões o ORM utilizará
```php
<?php
	$orm = ORM\Orm::getInstance();
	$orm->setConnectionsFile(__DIR__ . "/db/connections.php");
?>
```
Fonte: Autor, 2018

O arquivo deve conter um array com uma ou mais conexões, onde a chave da conexão é o nome identificador da conexão e o valor é um array contendo as informações da conexão. As informações variam de acordo com o banco de dados a ser utilizado.

Figura 3: Definindo arquivo de conexões
```php
<?php
	return [
		"exemplo-mysql" => [
			"db" => "mysql",
			"version" => "5.7.11",
			"host" => "localhost",
			"schema" => "app",
			"user" => "root"
			"pass" => "root"
		],
		"exemplo-sqlite" => [
			"db" => "sqlite",
			"version" => "3",
			"file" => "../data/app-storage.sq3",
		],
	];
?>
```
Fonte: Autor, 2018

Os valores para a conexão `exemplo-mysql` é um array contendo as chaves `db`, `version`, `host`, `schema`, `user` e `pass`. A chave `db` contém o banco de dados a ser utilizado. A chave `version` indica a versão do banco de dados utilizado, no qual o Driver deve corresponder à essa versão. A chave `host` é o endereço onde o banco de dados está localizado. A chave `schema` é o banco de dados (conjunto de tabelas) que será utilizado. As chaves `user` e `pass` são respectivamente o usuário e a senha de acesso ao banco de dados.

Os valores para a conexão `exemplo-sqlite` é um array contendo as chaves `db`, `version`, `file`. As chaves `db` e `version` funcionam da mesma maneira que a conexão anterior. A chave `file` indica o arquivo local o qual o banco de dados SQLite utilizará para armazenar os dados.
Para informar ao ORM qual (ou quais) conexão será utilizada na aplicação, deve ser feito através da classe principal do ORM conforme o exemplo a seguir:

Figura 4: Definindo conexões que o ORM poderá utilizar
```php
<?php
	$orm = ORM\Orm::getInstance();
	$orm->setConnection("exemplo-mysql");
	$orm->addConnection("exemplo-sqlite");
?>
```
Fonte: Autor, 2018

O método setConnection adiciona a conexão à lista de conexões que o ORM pode utilizar e faz com que a conexão informada seja a conexão padrão para o ORM, ou seja, qualquer operação que será realizada pelo ORM, se não for informada uma conexão explicitamente, o ORM irá assumir que a conexão que precisa ser usada é a conexão padrão.

Já o método addConnection apenas adiciona a conexão à lista de conexões que o ORM pode utilizar. A conexão padrão pode ser substituída a qualquer momento, para isso basta utilizar o método setDefaultConnection:
 
Figura 5: Definindo conexões que o ORM poderá utilizar
```php
<?php
	$orm = ORM\Orm::getInstance();
	$orm->addConnection("exemplo-mysql");
	$orm->setDefaultConnection("exemplo-mysql");
?>
```
Fonte: Autor, 2018

[Voltar](#tabela-de-conteúdo)

### 2.1. Criar Tabelas Automaticamente
---
O ORM tem a habilidade de criar as tabelas a partir das classes modelo. Para que o ORM saiba como criar, é necessário informar o caminho para a pasta que contém os modelos e o namespace no momento em que estiver configurando a conexão no ORM. Por exemplo:

Figura 6: Definindo criação de tabelas no ORM
```php
<?php
	$orm = ORM\Orm::getInstance();
	$orm->setConnection("exemplo-mysql", [
		"namespace" => "App\Models",
		"modelsFolder" => "/home/user/app/models",
		"create" => true
	]);
?>
```
Fonte: Autor, 2018

Pode ser necessário também, apagar as tabelas antes de criá-las, para isso, basta informar também na configuração da conexão:
 
Figura 7: Definindo criação e deleção de tabelas no ORM
```php
<?php
	$orm = ORM\Orm::getInstance();
	$orm->setConnection("exemplo-mysql", [
		"namespace" => "App\Models",
		"modelsFolder" => "/home/user/app/models",
		"create" => true,
		"drop" => true
	]);
?>
```
Fonte: Autor, 2018

O ORM permite ainda, que uma ação seja executada antes de apagar as tabelas e uma ação após criar as tabelas. Essas ações podem ser úteis para criar uma rotina de backup/restore ou de migração de banco de dados. Para informar ao ORM quais ações ele deve executar, basta fazer o seguinte:

Figura 8: Definindo ação para executar antes criação e da deleção de tabelas no ORM
```php
<?php
	$dbHelper = new App\Helpers\InitDatabase();
	$orm = ORM\Orm::getInstance();
	$orm->setConnection("exemplo-mysql", [
		"namespace" => "App",
		"modelsFolder" => "/home/user/app/models",
		"drop" => true,
		"create" => true,
		"beforeDrop" => [ $dbHelper, "beforeDrop" ],
		"afterCreate" => [ $dbHelper, "afterCreate" ]
	]);
?>
```
Fonte: Autor, 2018

Na linha 2, é criada uma instância da classe `App\Helpers\InitDatabase` e nas linhas 9 e 10, são informados, para a conexão respectivamente, quais métodos devem ser executados antes de apagar as tabelas e depois de criá-las. Utilizando esses métodos é possível que o desenvolvedor desenvolva uma lógica de como realizar o backup das informações essenciais do banco de dados antes de apagar as tabelas e posteriormente restaurar essas informações após a criação das tabelas.

Os valores esperados pelas chaves `beforeDrop` e `afterCreate` podem ser também uma função anônima:

Figura 9: Definindo ação para executar antes criação e da deleção de tabelas no ORM
```php
<?php
	...
		"beforeDrop" => function($entityManager) { ... },
		"afterCreate" => function($entityManager) { ... }
	...
?>
```
Fonte: Autor, 2018

Ou uma string contendo o nome de uma função:

Figura 10: Definindo ação para executar antes criação e da deleção de tabelas no ORM
```php
<?php
	...
		"beforeDrop" => "beforeDrop",
		"afterCreate" => "afterCreate"
	...

	function beforeDrop($entityManager) { ... }
	function afterCreate($entityManager) { ... }
?>
```
Fonte: Autor, 2018

O ORM passa uma instância de um EntityManager por parâmetro para os métodos ou funções que irão ser executados antes e depois do processo de criação das tabelas. Ele pode ser usado para realizar ações no banco de dados. O EntityManager será abordado mais à frente.

[Voltar](#tabela-de-conteúdo)

## 3. DEFININDO MODELOS
---
Um modelo é uma classe que representa uma tabela no banco de dados e pode ser mapeada da classe para a tabela e da tabela para a classe em operações de consulta, inserção, alteração e deleção.

Para que um modelo possa representar devidamente uma tabela no banco de dados dentro do ORM, ela deve ser "anotada" utilizando o padrão de annotation definido pelo ORM.

[Voltar](#tabela-de-conteúdo)

### 3.1. Annotations
---
As annotations são "etiquetas" que adicionam metadados relevantes sobre classes, métodos e propriedades. Ou seja, através do uso de annotations, pode-se adicionar às classes informações para mapear tabelas do banco de dados, e adicionar às propriedades da classe para mapear as colunas de uma tabela do banco de dados, para que posteriormente, em tempo de execução, os metadados indicados pelas annotations sejam analisados e a partir disso, o ORM irá trabalhar de acordo com essas informações.

Abaixo, uma lista completa das annotations e suas propriedades:

[Voltar](#tabela-de-conteúdo)

#### 3.1.1. Annotations de Classes
---

- __*Annotation:*__ @ORM/Entity.

	__Descrição:__ Define que a classe deve ser considerada como uma tabela no banco de dados.

	__Preenchimento:__ Obrigatório. O não preenchimento resulta em erro.

- __*Annotation:*__ @ORM/Table.

	__Descrição:__ Define informações sobre a tabela mapeada.

	__Preenchimento:__ Opcional.

	__Propriedades:__
	- __Nome:__ name.

		__Descrição:__ Define o nome da tabela mapeada.

		__Preenchimento:__ Opcional. Caso não preenchido, o ORM assume que o nome da tabela é o mesmo que o nome da classe.

	- __Nome:__ schema.

		__Descrição:__ Define qual é o conjunto de tabelas ou banco de dados ao qual a tabela mapeada em questão existe.

		__Preenchimento:__ Opcional. Caso não preenchido, o ORM assume que o schema a ser usado é o padrão definido na conexão ou nenhum, dependendo do banco de dados usado.

	- __Nome:__ mutable.

		__Descrição:__ Caso o valor seja `true` define que a tabela não pode ser modificada pelas operações de inserção, alteração e deleção. O valor padrão é "false". Normalmente utilizado para mapear uma view.

		__Preenchimento:__ Opcional. Assume o valor padrão caso não seja preenchido.


[Voltar](#tabela-de-conteúdo)

#### 3.1.2. Annotations de Propriedades
---

[Voltar](#tabela-de-conteúdo)

#### 3.1.3. Annotations de Relacionamentos
---

[Voltar](#tabela-de-conteúdo)

### 3.2. Exemplo de Mapeamento Simples
---

[Voltar](#tabela-de-conteúdo)

### 3.3. Exemplos de Mapeamento de Relacionamentos
---

[Voltar](#tabela-de-conteúdo)

#### 3.3.1. Um para Um
---

[Voltar](#tabela-de-conteúdo)

#### 3.3.2. Um para Muitos
---

[Voltar](#tabela-de-conteúdo)

#### 3.3.3. Muitos para Muitos
---

[Voltar](#tabela-de-conteúdo)

## 4. _ENTITY MANAGER_
---

[Voltar](#tabela-de-conteúdo)

### 4.1. Obtendo Uma Instância do _EntityManager_
---

[Voltar](#tabela-de-conteúdo)

### 4.2. Transações
---

[Voltar](#tabela-de-conteúdo)

#### 4.2.1. _BeginTransaction_
---

[Voltar](#tabela-de-conteúdo)

#### 4.2.2. _Commit_
---

[Voltar](#tabela-de-conteúdo)

#### 4.2.3. _Rollback_
---

[Voltar](#tabela-de-conteúdo)

### 4.3. _Find_
---

[Voltar](#tabela-de-conteúdo)

### 4.4. _List_
---

[Voltar](#tabela-de-conteúdo)

### 4.5. _QueryBuilder_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.1. Obtendo o _QueryBuilder_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.2. Criando uma consulta simples usando _list_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.3. Criando uma consulta simples usando _one_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.4. _Join_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.5. _Where_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.6. Paginação
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.7. _OrderBy_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.8. _GroupBy_
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.9. Agregação
---

[Voltar](#tabela-de-conteúdo)

#### 4.5.10. _Having_
---

[Voltar](#tabela-de-conteúdo)

### 4.6. _Save_
---

[Voltar](#tabela-de-conteúdo)

### 4.7. _Remove_
---

[Voltar](#tabela-de-conteúdo)

## 5. _LOGGER_
---

[Voltar](#tabela-de-conteúdo)

## 6. _DRIVER_
---

[Voltar](#tabela-de-conteúdo)
