# Registo de alterações do plugin Frigate

>**IMPORTANTE**
>
>Se não houver informações sobre a atualização, isso significa que esta diz respeito apenas à atualização da documentação, da tradução ou do texto.

# 11/05/2026 Beta e Versão Estável 1.5.5
- Correção de registos ERROR

# 06/05/2026 Beta e Estável 1.5.4
- novo botão para descarregar a lista de eventos (útil para a depuração de desenvolvimento)

# 19/04/2026 Beta 1.5.3
- Correção do comando curl para ativar e desativar as câmaras através da API

# 13/04/2026 Beta 1.5.2
- Correção do widget do painel de controlo

# 09/04/2026 Beta 1.5.1
- Melhoria do processo de limpeza de eventos
- Correção da atualização de data e hora no cron via http

# 05/04/2026 Versão estável 1.5.0
- Veja abaixo as informações sobre as versões Beta.

# 21/03/2026 Beta 1.4.97
- Adicionada a funcionalidade de ativação das câmaras através do MQTT
- Desativação do cron para as câmaras desativadas

# 21/03/2026 Beta 1.4.96
- Limpeza da lista de eventos

# 16/03/2026 Beta 1.4.95
- Limpeza dos nomes dos comandos da mesma forma que no Jeedom
- Correção da classificação «sub_label»; tinha colocado um «s» (sub_labels)

# 15/03/2026 Beta 1.4.94
- Funcionalidade para ordenar os pedidos (clique no nome ou no ID)

# 15/03/2026 Beta 1.4.93
- Criação de comandos para obter informações sobre os estados de classificação
- Adicionar botões de ligar/desligar no painel

# 10/03/2026 Beta 1.4.9
- Adicionados novos comandos para o reconhecimento facial
- Adicionados novos comandos para o reconhecimento da matrícula
- Adição dos novos comandos genAI
- Correções de um bug no Debian 12 e no Debian 13

# 09/11/2025 Beta 1.4.7
- Alteração da coluna «data» na base de dados
- Adicionar registos
- pequenas correções de erros

# 01/11/2025 Beta 1.4.6
- Adicionar comandos para o reconhecimento facial
- Adicionados comandos para o reconhecimento da matrícula

# 30/10/2025 Beta 1.4.5
- Adicionar posição no painel (tida em conta se existir a configuração Frigate)
- Adicionada configuração da qualidade e da resolução das capturas
- Possibilidade de converter as capturas em webp
- Caixa de seleção para ocultar os botões nos modelos do painel de controlo e do painel
- O widget do painel de controlo (e o design) é ajustável em altura e largura, de forma livre
- O painel de widgets tem dimensões fixas em altura e largura (435px e 315px)
- Correção de erros (obrigado @t0urista)

# 22/09/2025 Versão estável 1.4.2
- Correção do aviso do PHP
- Registos de correções

# 12/07/2025 Versão estável 1.4.0
- Adicionar a secção de perguntas frequentes à documentação

# 05/07/2025 Beta 1.3.6
- Adicionar a opção de descarregar ao clicar duas vezes.

# 04/07/2025 Beta 1.3.5
- Corrigir erro de JS no painel de controlo.
- Correção do plural nos eventos com duração de vários meses.
- Corrigida a largura da coluna nas páginas de eventos. (obrigado @vegeta0911)
- pop-up de eventos de estética.
- Adicionada a possibilidade de descarregar capturas de ecrã e vídeos.

# 08/06/2025 Versão estável 1.3.3
- Comparação dos eventos antes da recuperação
- Gestão da recuperação: 0 dias.

# 04/06/2025 Versão estável 1.3.2
- Adicionar as variáveis #camera#, #score# e #top_score# às condições

# 27/05/2025 Versão estável 1.3.1
- Correção numa nova instalação (erro da versão 1.3.0)
- O facto de ocultar no painel de controlo não significa que fique oculto no painel.

# 23/05/2025 Versão estável 1.3.0
- Versão mínima do Jeedom: 4.4
- Versão mínima do Debian: 11

# 08/05/2025 Versão estável 1.2.9
- Correção de erro caso o servidor não esteja ligado.

# 09/04/2025 Versão estável 1.2.5
- Adicionar comando «info uptime»
- Adicionar comando «info uptimeDate»

# 04/04/2025 Versão estável 1.2.4
- Adicionar comando «info description» (teste, mesmo assim, as ações do plugin)

# 02/04/2025 Versão estável 1.2.3
- Gestão da descrição do genAI
- Definir ações com base em condições

# 21/03/2025 Beta 1.2.2
- Adicionar condição para as ações

# 20/03/2025 Beta 1.2.1
- Adicionada caixa de seleção «autorizar ações» para os eventos dos equipamentos

# 18/03/2025 Versão estável 1.2.0
- Atualização com todas as correções anteriores.

# 18/03/2025 Beta 1.2.0
- Instantâneo do percurso de correção

# 27/02/2025 Beta 1.1.9
- Adicionadas as estatísticas da CPU e do armazenamento

# 23/02/2025 Beta 1.1.8
- Adição de registos frigateActions e frigateMQTT
- Correções no «snapshot» de variáveis nos tipos «update» e «new»
- Correção do URL da imagem (consulte a documentação caso seja necessário alterá-lo)

# 19/02/2025 Beta 1.1.7
- Gestão da zona de saída
- Correção na visualização do ficheiro de configuração do Frigate > 0.15

# 10/01/2025 Beta 1.1.6
- Correção do erro cronDaily no MySQL

# 11/11/2024 Versão estável 1.1.5
- Limpar o URL

# 23/10/2024 Beta 1.1.3
- Adicionar a zona às ações
- Correção da execução das ações

# 07/10/2024 Versão estável 1.1.2
- Verificação do estado do servidor Frigate antes de executar as tarefas cron

# 07/10/2024 Beta 1.1.1
- Adicionar comandos binários para os objetos detetados

# 05/10/2024 Versão estável 1.1.0
- Ver os detalhes das atualizações anteriores.

# 04/10/2024 Beta 1.0.6
- Correção da alteração do valor do áudio
- Atualização dos estados apenas se forem diferentes dos últimos

# 02/10/2024 Beta 1.0.5
- Correção de um erro na criação de comandos de áudio
- Correção de um erro na criação de comandos MQTT (valor reposto em 1)

# 01/10/2024 Beta 1.0.4
- Opção para excluir ou não os dados da cópia de segurança do Jeedom
- Adicionada a função de pausa PTZ
- Adicionada funcionalidade para verificar o estado e a disponibilidade do servidor
- Guardar automaticamente a bbox nos instantâneos
- Otimização do cron
- Correção do erro «file_get_content» caso os ficheiros não existam
- Correção do filtro de data (Firefox)
- Opção para visualizar as câmaras no painel

# 21/09/2024 Beta 1.0.3
- Opção para fluxos RTSP (ver documentação)
- Força o tipo genérico do snapshot do URL (efetue uma pesquisa ou guarde cada equipamento)
- Correção dos eventos da página de miniaturas (clip, pré-visualização, nada)

# 17/09/2024 Beta 1.0.2
- Adicionar a variável #preview# às notificações
- Na página «eventos», será apresentada a pré-visualização ao passar o cursor, além do vídeo (mais leve).
- Os filtros são guardados para serem aplicados na próxima vez que a página «eventos» for aberta.

# 16/09/2024 Beta 1.0.1
- Correção do seletor de predefinições no widget
- Correções de vários erros de JavaScript
- Adicionar a configuração de um link externo para aceder ao Frigate
- Se for utilizado o MQTT, as tarefas cron com intervalos inferiores a 30 minutos não serão executadas
- Nenhuma opção de registo de comandos será marcada nas novas instalações (nas restantes, lembre-se de as desmarcar)
- Adicionar um tempo de espera antes da recuperação dos instantâneos (a verificar!)
- É possível editar os nomes dos comandos predefinidos e HTTP
- Correção da caixa de seleção de exceção de condição, que só era aplicada à primeira ação

# 14/09/2024 Versão estável 1.0.0
- Tudo o que está nas versões beta anteriores.

# 14/09/2024 Beta 0.9.7
- Correções de erros HTTP_ERROR e JS
- Botão para editar o URL do comando HTTP
- Melhoria do painel
- Variáveis #user# e #password#, se necessário, nos comandos HTTP
- Reorganização das opções de informação e ações
- Configuração para integração automática no JeeMate v3
- caixa de seleção para ignorar a condição na ativação de ações

# 13/09/2024 Beta 0.9.6
- Correções nos comandos PTZ
- Adicionar botões PTZ ao widget
- Adicionar um botão para criar comandos HTTP (nome de utilizador e palavra-passe a introduzir na página da câmara)

# 11/09/2024 Beta 0.9.5
- Alteração na criação de pedidos.
- Comandos de áudio (estado, ligar, desligar e alternar) disponíveis, caso estejam presentes na sua configuração.
- Verificação da versão do Frigate uma vez por dia (se o cronDaily estiver ativado).
- Correção caso o nome já exista noutro local (oculto ou com maiúsculas)
- Alteração do widget no painel de controlo e no telemóvel
- Criação de comandos PTZ predefinidos (configuração a efetuar)

# 06/09/2024 Beta 0.9.4
- Adicionada a função «criar captura» (ver documentação)
- Adicionar painel

# 05/09/2024 Beta 0.9.3
- Adicionar a máscara à visualização das câmaras.
- Atualização dos instantâneos durante as receções internas.
- Várias alterações e melhorias na página «Eventos».
- A recuperação do evento no `createEvent` é mais rápida se o MQTT não estiver instalado.
- Traduções

# 19/08/2024 Beta 0.9.2
- Correção das ações do tipo «palavras-chave».
- Correção do filtro «tipo» na execução das ações.
- Correção dos acentos na criação de eventos.

# 17/08/2024 Beta 0.9.1
- Tradução para inglês, alemão, espanhol, italiano e português. Obrigado, @mips
- Correção da execução das ações.
- Nova gestão para a receção de eventos MQTT (Frigate 0.14).
- Correção relativa à criação de um evento manual.
- Melhoria da página de eventos.

# 10/08/2024 Beta 0.9.0
- Adicionar botão e opções para criar um evento.
- Correções do erro cron isFavorite.
- Adicionar um editor para o ficheiro de configuração (todas as alterações são por sua conta e risco; leia atentamente a documentação oficial do Frigate e faça uma cópia de segurança da configuração antes).
- Recuperação dos registos do servidor Frigate.
- Alteração na gestão da limpeza de pastas e eventos.
- Muitas outras alterações.

# 26/07/2024 Beta 0.8.2
- correções e recuperação de miniaturas
- Adicionar um botão para aceder aos eventos da câmara no widget
- Pequenas correções

# 26/07/2024 Beta 0.8.1
- correções, recuperação de clips e instantâneos
- alteração da cor dos botões do widget
- A pasta «data» já não é incluída nas cópias de segurança do Jeedom

# 22/07/2024 Beta 0.8.0
- Adicionar as variáveis #thumbnail_path# e #thumbnail#
- Adicionar dependência MQTT2
- Adicionar widget ao painel de controlo e ao telemóvel
- Adicionar evento aos favoritos
- Adicionar comandos para reiniciar (equipamento de estatísticas)
- Adicionar uma condição de execução às ações
- Criação dos comandos detect, snapshot e recording (iniciar, parar, alternar)
- Botão disponível para criar comandos PTZ
- Configuração do intervalo de atualização
- Configuração do tamanho máximo da pasta de cópias de segurança de instantâneos e clips
- Alteração da visualização do instantâneo
- Adicionar botão de depuração (ficheiro de configuração)
- Adicionar botão Discord
- Adicionar botão do servidor Frigate
- Várias pequenas correções

# 22/06/2024 Beta 0.7.5
- Adicionadas as variáveis #time#, #event_id#, #snapshot_path# e #clip_path#
- Adicionar botão para eliminar todos os eventos (ver documentação)
- Adicionar janela pop-up de confirmação antes da eliminação

# 20/06/2024 Beta 0.7.0
- Correção de um erro na criação de equipamentos
- Correção de erros na visualização da página «Eventos»
- Correções de erros do Cron
- Adicionar opções de filtragem à página «Eventos»
- Adicionar um link nos eventos para aceder à câmara e um link na câmara para aceder aos eventos.
- Adicionar às ações um campo de etiqueta (pode estar vazio, conter «all» ou o nome da etiqueta), para que a ação seja acionada apenas para uma etiqueta específica.

# 17/06/2024 Beta 0.6.0
- Adicionar registos
- Adicionar comandos no equipamento «Events» para ativar o cron
- Alteração da configuração do cron; utilize as caixas de seleção do Jeedom.
- Adicionadas opções à página de eventos (obrigado, @noodom)
- Configuração predefinida da divisão

# 15/06/2024 Beta 0.5.0
- primeira versão beta
