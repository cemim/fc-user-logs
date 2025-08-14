=== FC User Logs ===
Contributors: filipecemim
Tags: user, logs, learndash, activity, audit
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

FC User Logs registra alterações realizadas nos usuários do WordPress, compatível com o LMS LearnDash, oferecendo uma visão completa das mudanças feitas no site.

== Description ==

O FC User Logs é um plugin para WordPress que monitora e registra alterações nos dados dos usuários, incluindo campos principais, metadados e eventos do LearnDash (como adição e remoção de grupos).  

**Funcionalidades principais:**
* Registro de alterações nos dados do usuário (login, nome, e-mail, etc.)
* Registro de alterações nos metadados do usuário
* Monitoramento de eventos do LearnDash:
    * Adição de grupo ao usuário
    * Remoção de grupo do usuário
* Tela administrativa para visualização dos logs
* Opções de configuração de limpeza automática:
    * Quantidade de dias para manter os logs
    * Limite máximo de registros
* Estilos personalizados para o painel administrativo

**Benefícios:**
* Permite auditoria completa das ações feitas em usuários
* Auxilia no acompanhamento de alunos em plataformas de LMS (LearnDash)
* Mantém o banco de dados limpo através da limpeza automática

== Installation ==

1. Faça o download do plugin ou clone o repositório.
2. Coloque a pasta do plugin em `wp-content/plugins/fc-user-logs`.
3. Ative o plugin em **Plugins > Plugins instalados**.
4. O plugin criará automaticamente a tabela `wp_user_logs_fc` para armazenar os logs.
5. Acesse o menu **FC User Logs** no painel administrativo para visualizar os logs.

== Screenshots ==

1. Tela principal do plugin exibindo os logs de usuários
2. Página de visualização detalhada de um log
3. Tela de configuração da limpeza automática de registros
4. Exemplo de log de alteração de usuário

== Frequently Asked Questions ==

= O plugin funciona com qualquer tema? =
Sim, o plugin funciona independentemente do tema ativo, pois utiliza hooks do WordPress e do LearnDash.

= Posso configurar quantos dias manter os logs? =
Sim, na página "Limpeza Automática" é possível definir tanto a quantidade de dias para manter os logs quanto o limite máximo de registros.

= O plugin registra alterações de qualquer usuário? =
Sim, qualquer alteração feita em usuários pelo painel administrativo será registrada, incluindo alterações manuais e via LearnDash.

= Como visualizar logs de alterações do LearnDash? =
Acesse **FC User Logs > View Logs** e procure por campos `learndash_group_access_added` ou `learndash_group_access_removed`.

== Detailed Usage ==

Após ativar o plugin:

1. **Visualizar Logs:**
   - Menu: **FC User Logs > View Logs**
   - Mostra: Usuário alterado, usuário que realizou a alteração, campo alterado, valor antigo, valor novo e data/hora.
   
2. **Limpeza Automática:**
   - Menu: **FC User Logs > Limpeza Automática**
   - Defina quantos dias manter os logs (`Registros antigos que excederem essa data serão apagados`)  
   - Defina limite máximo de registros (`Se houver mais registros que o limite, os mais antigos serão apagados`)  
   - Botão **Executar limpeza agora** para limpar manualmente.

3. **Integração LearnDash:**
   - Alterações em grupos de usuários (adição ou remoção) são registradas automaticamente.
   - Campos no log:  
     `field_name: learndash_group_access_added`  
     `field_name: learndash_group_access_removed`

== Changelog ==

= 1.0 =
* Lançamento inicial
* Monitoramento de alterações de usuários
* Monitoramento de alterações em metadados
* Integração com LearnDash
* Tela administrativa para visualização de logs
* Configuração de limpeza automática

== Upgrade Notice ==

= 1.0 =
Primeira versão. Nenhuma ação necessária ao atualizar.

== Arbitrary Section ==

**Hooks principais:**
* `profile_update` – captura alterações de usuários
* `ld_added_group_access` – captura adição de grupo no LearnDash
* `ld_removed_group_access` – captura remoção de grupo no LearnDash
* `fc_user_logs_cleanup_cron` – limpeza automática de logs

**Desenvolvimento:**
* A tabela de logs criada automaticamente: `wp_user_logs_fc`
* Logs armazenam:
  * ID do usuário alterado
  * Usuário que realizou a alteração
  * Campo alterado
  * Valor antigo
  * Valor novo
  * Data/hora da alteração

== License ==

GPLv2 or later
