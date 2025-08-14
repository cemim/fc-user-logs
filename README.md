# FC User Logs

**FC User Logs** é um plugin WordPress que registra logs de alterações nos usuários, compatível com o LMS LearnDash.

## Descrição

Este plugin permite monitorar mudanças em usuários e metadados do WordPress, incluindo ações relacionadas ao LearnDash, como adicionar ou remover usuários de grupos. Ele também oferece limpeza automática de logs antigos ou excesso de registros.

## Recursos

- Registro de alterações em usuários e metadados (`wp_users` e `wp_usermeta`)
- Compatível com LearnDash (log de adição e remoção de grupos)
- Visualização dos logs no painel do WordPress
- Opção de definir quantidade de registros por página
- Limpeza automática ou manual de registros antigos
- Totalmente compatível com WordPress 6.8+ e PHP 8.0+

## Instalação

1. Faça o download do plugin.
2. Copie a pasta `fc-user-logs` para o diretório `/wp-content/plugins/` do seu site WordPress.
3. Ative o plugin através do menu “Plugins” no WordPress.
4. Acesse o menu **FC User Logs** para visualizar logs e configurar opções de limpeza.

## Requisitos

- WordPress 6.5 ou superior
- PHP 7.4 ou superior
- LMS LearnDash (opcional, apenas para integração com grupos)

## Uso

Após ativar o plugin:

1. No menu lateral do WordPress, vá em **FC User Logs > View Logs** para ver alterações.
2. Configure a **Limpeza Automática** em **FC User Logs > Limpeza Automática**.
3. O plugin registra alterações de usuários e metadados automaticamente, incluindo alterações feitas via LearnDash.

## Suporte

Para relatar bugs ou solicitar recursos, utilize o repositório no GitHub:  
[https://github.com/cemim/fc-user-logs](https://github.com/cemim/fc-user-logs)

## Licença

GPL v2 ou superior – veja [GNU GPL v2](https://www.gnu.org/licenses/gpl-2.0.html) para detalhes.