# My Micro certs

next release:
- adicionar categorias (id/empresa/nome)
- adicionar vinculo entre categorias e perguntas, uma pergunta pode ter apenas 1 categoria
- remover vinculo pergunta -> exame
- alterar POST /exam para receber array de questions, ou array de categorias
    - neste caso, caso sejam utilizados os dois, o exame pode conter perguntas fixas e aleatorias, jamais repetindo as perguntas
    - no momento de criacao do exame nao é verificado a validade da combinaçao escolhida
    - cada categoria deve conter também o numero de questoes necessário
    
exemple 
```json
{
	"questions": [
		"804644557-804644557-804644557",
		"274313233-274313233-274313233",
		"134600426-134600426-134600426"
	],
	"categories": [
		{"id": "1722258350-1722258350-1722258350", "quantity": 8},
		{"id": "1056008808-1056008808-1056008808", "quantity": 6},
		{"id": "2056308422-2056308422-2056308422", "quantity": 3}
	]
}
```