Plugin criado por **Sagitaz** e **Noodom**

# <u>Agradecimentos</u>
O plugin e o suporte são gratuitos; no entanto, se quiser oferecer-me um café ou fraldas para bebé, agradeço desde já.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/C1C61AKVV7)

# <u>Ajuda e Suporte</u>
- Comunidade Jeedom
- Discord JeeMate

Para qualquer pedido de ajuda na Community ou no Discord, por favor, forneça o máximo de informações possível. (equipamento, tipo de câmara, versão do Jeedom, do Frigate, do seu sistema, etc...)

Na página de configuração, o botão «Assistência» já permite preencher automaticamente algumas opções.

Certifique-se de que dispõe do equipamento compatível com o Frigate e de que este funciona corretamente antes de solicitar ajuda relativamente ao plugin. (Consulte a documentação oficial do Frigate para conhecer as configurações de hardware recomendadas).

Forneça também os registos no modo de depuração (os do plugin e do servidor Frigate).

Não será prestado qualquer apoio através de outros meios de comunicação que não sejam estes.

Obrigado


# <u>Requisitos prévios</u>
- Jeedom 4.4.0, no mínimo
- Debian 11 (Bullseye) ou superior
- Frigate 0.16.0, no mínimo

O plugin não instala nem configura o servidor Frigate; por isso, terá de o instalar e configurar você mesmo. Consulte a documentação oficial do Frigate para obter mais informações.

# <u>Instalação</u>
Tal como acontece com todos os outros plugins, depois de o instalar, é necessário ativá-lo.

O plugin continuará a ser compatível com a última versão estável conhecida (enquanto se adapta). No entanto, não iremos realizar vários desenvolvimentos para garantir a compatibilidade com versões anteriores. Por isso, se algo não funcionar, comece por atualizar o seu servidor Frigate antes de pedir ajuda.

A 30 de setembro de 2026, o plugin funciona com as seguintes versões do Frigate:
- Frigate 0.18.0 Estável

A versão mínima é a Frigate 0.16.0. Algumas funcionalidades só estão disponíveis a partir da Frigate 0.18: estado dos fluxos das câmaras, perfis e parâmetro pre_capture dos eventos criados manualmente. Não garanto o suporte às versões anteriores do servidor Frigate.

# Modo de ligação ao servidor Frigate
### API
Recuperação de imagens das câmaras, de eventos antigos, eliminação de eventos, etc...
Muitas das funcionalidades do plugin utilizam a ligação à API Frigate.
Esta funcionalidade está acessível através da porta 5000 na sua rede local; é imprescindível que a tenha configurado, caso contrário, o plugin não funcionará.
Se pretender utilizar outra porta, pode fazê-lo desde que efetue o mapeamento para a porta 5000 (5054:5000) na configuração do servidor Frigate.
### MQTT
A configuração MQTT permite receber informações do servidor Frigate em tempo real.
Isto melhora a experiência do utilizador com o plugin, mas não é necessário para o seu funcionamento.
É obrigatório um broker seguro (user:mdp) para que o plugin mqtt-manager, do qual o plugin frigate depende, funcione corretamente.


# <u>Registo</u>
O plugin inclui subregistos; para que estes sejam visíveis no Jeedom 4.4.19, é necessário definir os registos globais com um nível de informação mínimo.

![nível de registos](../images/frigate_Doc_Logs.png)
# <u>Configuração</u>
- **Divisão predefinida**: As câmaras criadas serão automaticamente colocadas nesta divisão.
- **Excluir do backup**: Se esta opção estiver marcada, a pasta «data» do plugin (instantâneos, clipes, miniaturas e capturas) é excluída dos backups do Jeedom, que ficam assim mais leves. Após a restauração de um backup deste tipo, os eventos deixam de ter os seus ficheiros: em vez disso, é apresentada uma imagem predefinida.
- **Versão do plugin**: a versão instalada, apenas para leitura. Indique-a nos seus pedidos de ajuda.

#### Configuração do Frigate
- **URL**: a URL do seu servidor Frigate (por exemplo: 192.168.1.20)
- **Porta**: a porta do servidor Frigate (5000 por predefinição); pode utilizar outra porta, desde que esteja mapeada para a 5000 (por exemplo, 5054:5000); sem isso, a API não funcionará.
- **Endereço externo**: Para aceder à página do servidor Frigate a partir do exterior.
- **Tópico MQTT**: o tópico do seu servidor Frigate (frigate por predefinição)
- **Predefinição**: Para câmaras com PTZ, defina o número de posições que pretende recuperar.
- **Pausa de ação**: Pausa a efetuar nas ações PTZ. Por exemplo, depois de premir «move up», é automaticamente efetuada uma paragem: pode definir o tempo antes desta ação de paragem entre 0 e 10, o que corresponde a uma pausa de 0 a 1 segundo (0, 0,1, 0,2, etc...).

#### Gestão de eventos
- **Recuperação de eventos**: Pode ter 30 dias de eventos no seu servidor Frigate, mas querer importar apenas 7 para o Jeedom. Indique aqui o número de dias pretendido (7 por predefinição). Se o número de dias for 0, o processo é interrompido e não é efetuada qualquer chamada à API do Frigate.
- **Eliminação de eventos**: Os eventos com uma antiguidade superior ao número de dias indicado (7 por predefinição) serão eliminados da base de dados Jeedom juntamente com os respetivos ficheiros, mas não do servidor Frigate.

O número de dias de supressão não pode ser inferior ao número de dias de recuperação. Caso contrário, será utilizado o número de dias de recuperação.

- **Tamanho das pastas**: Tamanho máximo da pasta «data», em MB (500 MB por predefinição). Se esse limite for ultrapassado, os eventos mais antigos são eliminados até que o tamanho volte a ficar abaixo do limite.

Os eventos marcados como favoritos nunca são eliminados, nem por antiguidade nem por tamanho. As capturas manuais seguem as mesmas regras que os eventos: marque-as como favoritas para as guardar.

- **Intervalo de atualização**: Em segundos, o intervalo de atualização das imagens das suas câmaras. (5 segundos por predefinição). Cada câmara pode ter o seu próprio intervalo; consulte as especificações da câmara.
- **Vídeos em miniaturas**: Ao passar o rato sobre uma miniatura na página do evento, o vídeo será reproduzido.
- **Confirmação antes da eliminação**: Apresenta um aviso antes da eliminação de um evento.
- **Pausa na criação de ficheiros (em segundos)**: Tempo de espera antes da criação do ficheiro (clip / instantâneo) (5 s por predefinição). Dependendo dos servidores, isto pode ser necessário para dar tempo ao Frigate a criar o ficheiro.
#### Configurações predefinidas de um evento criado manualmente
- **Etiqueta**: o nome do evento criado (por predefinição, «manual»).
- **Gravar um vídeo**: sim, por predefinição.
- **Duração do vídeo**: 40 segundos por predefinição.
- **Pontuação**: 0 por predefinição.

#### Funcionalidades
- **Cron**: selecione o cron pretendido.


# <u>Demon</u>
O daemon inicia automaticamente após guardar a secção de configuração e ter configurado aí o tópico «Frigate».
Para poder utilizar o MQTT, é necessário que tenha configurado corretamente o seu servidor Frigate e que tenha o plugin mqtt-manager (mqtt2) instalado e configurado corretamente.
O seu broker MQTT tem de estar protegido para que o plugin mqtt-manager funcione.
Se utilizar o MQTT, pode definir o cron para «Hourly» ou «Daily».

**Deamon NOK:**
Se não tiver o mqtt-manager, é normal que o daemon permaneça em NOK. Não há problema, o plugin funciona na mesma, mas algumas funções estarão indisponíveis ou limitadas.

# <u>Utilização</u>

**Os comandos de informação de todos os equipamentos são criados automaticamente na próxima receção de eventos ou estatísticas. Se não os vir logo após a instalação inicial do plugin, significa que os seus eventos recentes têm mais de 3 horas; por isso, terá de aguardar o próximo evento para ver os comandos.**

**Os comandos de ação só são criados quando utiliza o botão «Pesquisar / Atualizar».**

## <u>Equipement Events</u>
O equipamento é criado automaticamente ao mesmo tempo que as câmaras.
Este inclui comandos informativos com o valor do último evento recebido.
Inclui também dois comandos de ação: «cron start» e «cron stop», que servem para suspender a procura de novos eventos.

É possível criar ações comuns a todas as câmaras (ver a secção dedicada)
Assinale a opção «autorizar ações» se pretender que, aquando de uma deteção, sejam executadas as ações presentes nos eventos do equipamento e nas câmaras.


## <u>Equipamento Estatísticas</u>
O equipamento é criado automaticamente ao mesmo tempo que as câmaras.
Este inclui comandos de informação com algumas estatísticas disponíveis.

Inclui também o comando «action», que permite reiniciar o servidor Frigate.

Com o Frigate 0.18 ou superior e o MQTT, dois comandos ocultos por predefinição permitem monitorizar e alterar o perfil ativo do Frigate:
- **Perfil ativo**: nome do perfil ativo, ou nenhum
- **Alterar perfil**: indicar na mensagem o nome do perfil ou «none» para não ativar nenhum

## <u>Equipamento de câmaras</u>
Após a instalação do plugin e a configuração do URL e da porta do seu servidor Frigate, basta clicar no botão «Pesquisar». As câmaras encontradas serão criadas automaticamente. É necessário aguardar, pois na primeira pesquisa são também importados os eventos do último dia. Este processo pode demorar algum tempo.

### Equipamento

- **Nome de utilizador** e **palavra-passe**: úteis apenas para comandos HTTP (ver mais abaixo).
- **Atualização**: tempo de atualização da imagem da câmara, em segundos: o primeiro valor para o painel de controlo e o painel, o segundo para o JeeMate. Se não for indicado nenhum valor, é utilizada a duração definida na configuração geral.
- **Mostrar no painel**: assinale esta opção para que a câmara fique visível no painel.
- **Posição no painel**: ordem de exibição da câmara no painel (1, 2, 3…). As câmaras sem posição são exibidas a seguir às outras. Aquando da criação da câmara, a posição assume a ordem definida no Frigate (**``ui -> order``**).
- **Fluxo de vídeo**: Indique um fluxo diferente do predefinido, caso este não seja adequado (rtsp://URL_Frigate:8554/Nome_da_câmara)
- **Número de predefinições**: número de predefinições PTZ a importar, caso pretenda um número diferente da configuração global (máximo de 10).
- **Qualidade dos instantâneos**: qualidade de compressão das imagens descarregadas, de 1 a 100 (70 por predefinição). Quanto mais baixo for o valor, mais leves serão os ficheiros. Aplica-se a instantâneos, miniaturas e capturas.
- **Altura dos instantâneos**: altura máxima das imagens, em píxeis. Uma imagem mais alta é reduzida mantendo as suas proporções. Se não for indicado nenhum valor, o tamanho original é mantido. Não se aplica às miniaturas.
- **Converter para WEBP**: as imagens são guardadas no formato WebP, mais leve do que o JPEG.
- **Modelo de painel de controlo** e **Modelo de painel**: apresentar apenas a imagem da câmara, no painel de controlo ou no painel. Os botões, os comandos PTZ e os ícones de deteção ficam ocultos; um clique na imagem abre sempre a janela ampliada com as ações.

A qualidade, a altura e o formato aplicam-se apenas às imagens carregadas após terem sido editadas.

À direita, encontram-se os parâmetros disponíveis para visualização.
Atualize a imagem de acordo com a sua configuração.

- bbox
- timestamp: a data, que também constará no instantâneo criado, caso esta opção esteja marcada.
- zonas
- máscara: a área será ocultada
- movimento: a área está contornada a vermelho
- região: a zona a leste com um contorno verde

### Comandos e informações
##### Todas as câmaras
Informações sobre o último evento da câmara: câmara, etiqueta, pontuação, pontuação máxima, zonas, ID, tipo, data e hora, duração, vídeo disponível, imagem disponível, URL da imagem, URL do vídeo e URL da miniatura. E as estatísticas da câmara.

A informação **LABEL** corresponde ao objeto que desencadeou a deteção (pessoa, veículo, gato, cão, etc...)

- **RTSP**: o link do fluxo de vídeo da câmara (ver a secção «Fluxo de vídeo»).
- **SNAPSHOT LIVE**: o link para a imagem em direto da câmara, para os plugins que apresentam uma imagem da câmara.

##### MQTT
- **Detecção em curso**: assim que o Frigate deteta uma alteração, o valor passa para 1 (nuvens, luminosidade, pessoa, etc...)
- **Detecção xxx**: para cada câmara será adicionado um estado que indica se está em curso uma detecção ativa ou não para cada objeto configurado. Por exemplo, se tiver uma câmara com uma pessoa, um veículo, uma vaca, etc., terá 3 estados: pessoa, vaca, veículo. Se marcar a opção «visível», o ícone aparecerá no widget quando houver uma detecção. O ícone pode ser personalizado nas definições do comando. Se um objeto for considerado estático, a deteção volta a 0.
- **Detecção all**: Se for detetado um objeto em movimento, o comando passa para 1. Quando o Frigate deixa de detetar movimento ou o objeto fica imóvel, o comando volta a 0. Se o comando all estiver em 0, os restantes comandos de deteção serão forçados a 0.
- **Estado dos fluxos de deteção / gravação / áudio** (Frigate 0.18 ou superior, ocultos por predefinição): online, offline ou desativado para cada fluxo da câmara. O Frigate reinicia um fluxo que esteja offline, pelo que o valor pode alternar entre offline e online: aguarde até que se mantenha estável antes de agir, por exemplo, com uma condição de duração no cenário.

##### Agradecimentos
Se o reconhecimento facial, a leitura de matrículas, os modelos de classificação ou a IA generativa estiverem ativados no Frigate, a câmara recebe, através do MQTT, o resultado do último reconhecimento. Os comandos são criados com base no primeiro resultado.
- **Reconhecimento - Tipo**: rosto, LPR (matrícula), classificação ou descrição
- **Reconhecimento - Nome** e **Reconhecimento - Pontuação**: a pessoa ou a matrícula reconhecida, ou o modelo de classificação, com a pontuação em %
- **Reconhecimento - Matrícula**: a matrícula lida
- **Reconhecimento - Rótulo** e **Reconhecimento - Atributos**: o resultado de um modelo de classificação de objetos
- **Reconhecimento - Descrição**: a descrição gerada pela IA
- **Reconhecimento - Estado xxx**: o estado atual de um modelo de classificação de estado configurado para a câmara

### Comandos e ações
- **Criar um evento**: consulte a página «Eventos».
- **Captura**: estado, captura (ver «Criação de uma captura instantânea»).
- **(Config) Câmara**: estado, ativar, desativar, alternar. Estes comandos alteram o ficheiro de configuração do Frigate: é necessário reiniciar o servidor para que as alterações tenham efeito.

Para dispor dos seguintes comandos de ação, é obrigatório utilizar o MQTT. Caso contrário, os comandos não serão criados. Recomendo que consulte a documentação do Frigate para configurar o seu servidor MQTT. Cada um tem um estado e os comandos «on», «off» e «toggle».

- **Detect**: deteção de objetos
- **Snapshot**: instantâneos de eventos
- **Gravação**: gravação
- **Movimento**: deteção de movimento (a opção «OFF» só é possível se a opção «detect» também estiver em «OFF»)
- **ativado**: ativa ou desativa a câmara imediatamente, sem alterar o ficheiro de configuração. A partir da versão 0.18 do Frigate, o estado é mantido quando o Frigate é reiniciado; anteriormente, a câmara voltava à sua configuração original.
- **review_alerts** e **review_detections**: alertas e deteções das atividades da câmara, até ao reinício do Frigate. O plugin recebe os novos eventos em tempo real através das atividades: sem alertas nem deteções, deixa de receber novos eventos.
- **review_descriptions** e **object_descriptions**: descrições geradas por IA generativa das atividades e dos objetos monitorizados, até ao reinício do Frigate
- **notificações**: notificações do Frigate para a câmara (não as do Jeedom)
- **improve_contrast**: melhoria do contraste para a deteção de movimento

Os comandos PTZ, predefinições e áudio só são criados se a configuração do seu servidor Frigate contiver essas informações.
- **PTZ**: para a esquerda, para a direita, para cima, para baixo, parar, ampliar, reduzir
- **Áudio**: estado, ligado, desligado, alternar
- **Predefinição**: a ação que permite posicionar a câmara num ponto específico.

### Comandos HTTP
No separador **PTZ & HTTP** de uma câmara, o botão **Adicionar um comando HTTP** cria um comando de ação que acede ao URL indicado, por exemplo, para controlar uma função da câmara que o Frigate não disponibiliza.

Na URL, **``#user#``** e **``#password#``** são substituídos pelo nome de utilizador e pela palavra-passe do equipamento, por exemplo:
**``http://192.168.1.50/cgi-bin/api.cgi?cmd=Snap&user=#user#&password=#password#``**

A chamada também utiliza a autenticação Digest com este identificador e esta palavra-passe. A resposta da câmara é registada no comando info **Estado HTTP do comando**. A palavra-passe é ocultada nos registos.

Os comandos HTTP são criados de forma oculta. Assim que forem tornados visíveis, aparecem na lista suspensa de ações do widget, juntamente com as predefinições. O botão com o ícone do lápis do comando permite alterar o seu URL.

### Ação(ões) em caso de evento
As ações em resposta a eventos estão disponíveis para o equipamento **Eventos** e para cada equipamento **câmaras**.
As ações configuradas no equipamento **Events** serão executadas pelos eventos provenientes de todas as câmaras, **a menos que estas tenham ações configuradas e ativadas.**
Se pretender agrupar ações comuns no equipamento «Events» e, em seguida, adicionar ações para cada câmara, não se esqueça de marcar a caixa «autorizar ações» no equipamento «Events».

<u>Desenvolvimento da ação</u>:


![execução de uma ação](../images/frigate_Doc_ActionsEvents.png)
#### Condições gerais
Indique aqui em que casos as ações **NÃO DEVEM** ser executadas.

Por exemplo, configura-se a condição da seguinte forma:
**#[Casa][Moda para a casa][Moda]# == «presente»**
As ações só serão executadas se o modo for qualquer outro que não o atual.


#### Ações
Pode indicar aqui as ações a realizar sempre que ocorrer um novo evento.

Uma caixa de seleção permite-lhe desativar a verificação da condição geral.

<u>LABEL</u> :
**Recorde-se que é a etiqueta que desencadeia a deteção (pessoa, veículo, animal, etc.)**
No campo **label**, basta indicar o(s) label(s) para o(s) qual(is) pretende que a ação seja executada.
Se este campo estiver **vazio** ou se introduzir **all**, a ação será executada para todos os novos eventos.
Pode indicar várias etiquetas, separando-as por vírgulas.
As maiúsculas e os acentos são ignorados; por isso, se indicar «Velo» ou «velo», ambas as formas serão consideradas idênticas.

<u>TIPO</u>:
**Com** o MQTT, podem ser do tipo **new**, **update** e **end**.
**Sem** MQTT, será sempre do tipo **end**.
No campo **tipo**, basta indicar o tipo para o qual pretende que a ação seja executada.
Pode indicar vários, separando-os por vírgulas.
Se não for especificado nenhum tipo, a ação será executada apenas para eventos do tipo **end**.
As maiúsculas e os acentos são ignorados; por isso, se indicar «update» ou «UPDATE», ambos serão considerados idênticos.

<u>ZONAS</u>:

No campo **zona de entrada**, basta indicar a zona ou as zonas para as quais pretende que a ação seja executada.
Pode indicar várias zonas, separando-as por vírgulas.

A caixa **zona de saída** permite gerir o sentido da deteção. Esta funcionalidade só funciona se estiver definida uma zona de entrada. Se a zona de entrada for ativada antes da zona de saída, a ação será executada.

As maiúsculas e os acentos são ignorados; por isso, se indicar «Allée» ou «allee», ambas as formas serão consideradas idênticas.

<u>CONDIÇÕES DA PROMOÇÃO</u>:
Indique aqui em que casos as ações **DEVEM** ser executadas.

Por exemplo, configura-se a condição da seguinte forma:
**#[Casa][Moda para a casa][Moda]# == "ausente"**
As ações só serão executadas se o modo estiver configurado como «ausente».

Se não for especificada nenhuma condição, a ação será executada.

<u>INFORMAÇÕES ÚTEIS</u> :
- Uma ação que utilize **#clip#** ou **#clip_path#** só é executada se o clip estiver disponível. Da mesma forma, uma ação que utilize **#snapshot#** ou **#snapshot_path#** só é executada se o snapshot estiver disponível.
- Um evento que teve início há mais de 3 horas não desencadeia nenhuma ação, por exemplo, aquando da recuperação de eventos anteriores.

<u>Variáveis disponíveis para as condições:</u>
- **#câmara#**: o nome da câmara
- **#score#**: pontuação em percentagem -> 82 %
- **#top_score#**: a pontuação máxima em percentagem -> 92 %

<u>Variáveis disponíveis para as ações:</u>
Está disponível uma lista de variáveis para personalizar as ações; estas variáveis são substituídas pelos seus valores quando a ação é executada.
- **#time#**: a hora atual no formato 12:00
- **#event_id#**: o identificador Frigate do evento
- **#type#**: o tipo do evento: new, update ou end
- **#câmara#**: o nome da câmara
- **#cameraId#**: o ID da câmara (por exemplo, um deeplink para a página da câmara na aplicação JeeMate)
- **#score#**: pontuação em percentagem -> 82 %
- **#has_clip#**: texto 0 ou 1
- **#has_snapshot#**: texto 0 ou 1
- **#top_score#**: a pontuação máxima em percentagem -> 92 %
- **#zonas#**: texto, as zonas separadas por vírgulas
- **#description#**: a descrição do evento gerada pelo genAI (é claro que é necessário tê-lo ativado no servidor Frigate)
- **#sublabel#**: o rótulo atribuído por um modelo de classificação de objetos da Frigate
- **#atributos#**: os atributos atribuídos por um modelo de classificação de objetos da Frigate
- **#snapshot#**: ligação para o ficheiro de imagem
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#snapshot_path#**: caminho para o ficheiro de imagem
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_snapshot.jpg`
- **#clip#**: ligação para o ficheiro mp4
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#clip_path#**: caminho para o ficheiro mp4
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_clip.mp4`
- **#thumbnail#**: link para o ficheiro de imagem
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#thumbnail_path#**: caminho para o ficheiro de imagem
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_thumbnail.jpg`
- **#preview#**: link para o ficheiro de pré-visualização
`https://URL/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#preview_path#**: caminho para o ficheiro de pré-visualização
`/var/www/html/plugins/frigate/data/frigate1/1718992955.613576-zulr2q_preview.gif`
- **#label#**: texto
- **#start#**: hora de início
- **#end#**: hora de fim
- **#duree#**: duração do evento
- **#jeemate#**: ver explicações mais abaixo



### Exemplos de notificações:
#### Plugin JeeMate
- **snapshot**: no campo «título»: **``title=o seu título;;bigPicture=#snapshot#``**
- **pré-visualização**: no campo «título»: **``title=o seu título;;bigPicture=#preview#``**
- **miniatura**: no campo «título»: **``title=o seu título;;bigPicture=#thumbnail#``**
- **clip**: no campo «título»: **``title=o seu título;;bigPicture=#clip#``**

Para receber uma notificação automática, adicione frigate=#jeemate#, disponível na futura versão 3 do JeeMate

- **snapshot**: no campo «título»: **``title=o seu título;;bigPicture=#snapshot#;;frigate=#jeemate#``**
- **clip**: no campo «título»: **``title=o seu título;;bigPicture=#clip#;;frigate=#jeemate#``**

#### Plugin do Telegram
Experimente os dois comandos «snapshot». Dependendo das configurações, é possível que um deles não funcione.
- **snapshot**: no campo «opções»: **``title=o seu título | snapshot=#snapshot#``**
- **instantâneo**: no campo «opções»: **``title=o seu título | file=#snapshot_path#``**
- **clip**: no campo «opções»: **``title=o seu título | file=#clip_path#``**
- **pré-visualização**: no campo «mensagem»: **``#preview#``**

#### Plugin Mobile v2
- **snapshot**: no campo «mensagem»: **``a sua mensagem | file=#snapshot_path#``**
- **clip**: não faço ideia

#### Plugin JeedomConnect
- **instantâneo**: no campo «título»: **``title=o seu título | files=#snapshot_path#``**
- **clip**: no campo «título»: **``title=o seu título | files=#clip_path#``**

#### Plugin NTFY
- **snapshot**: no campo «Opções»: **``Título: o seu título; Anexar: #snapshot#``**
- **clip**: no campo «Opções»: **``Título: o seu título; Anexar: #clip#``**

# <u>Página de eventos</u>

### Evento:
![quadro do evento](../images/frigate_Doc_Evenement.png)
1. - Adicionar o evento aos favoritos
2. - ligação para a câmara
3. - Visualizar o instantâneo (basta clicar no ícone)
   - Descarregar o snapshot (clique duas vezes no ícone)
4. - Ver o vídeo (basta clicar no ícone)
   - Descarregar o vídeo (clique duas vezes no ícone)
5. - Eliminar o evento
6. - Descrição do evento (se o genAI estiver ativado)

**ATENÇÃO**: o botão «**eliminar**» elimina o evento da base de dados do Jeedom, mas também do seu servidor Frigate. Em caso algum serei responsável pelo uso indevido deste botão. No entanto, é apresentada uma janela pop-up de confirmação.

Os eventos marcados como favoritos não são eliminados.


![página de eventos](../images/frigate_Doc_Evenements.png)

1. - **eliminar todos os eventos visíveis**
**ATENÇÃO**: O botão «**eliminar todos os eventos visíveis**» fará exatamente o que indica, por isso certifique-se de que aplica os filtros corretos antes de eliminar: não será possível voltar atrás: é apresentada uma janela pop-up de confirmação. A eliminação é efetuada na base de dados Jeedom, mas também no seu servidor Frigate.

2. - **Criação de um evento manual**
Na configuração geral do plugin Frigate, pode definir os valores predefinidos dos eventos criados manualmente.
Na página **Eventos**, encontrará um botão que lhe permite criar um novo evento.
Para cada câmara, um comando de ação também lhe permitirá criar um evento.
Este comando é do tipo mensagem. Se o deixar em branco, serão utilizados os parâmetros predefinidos (a partir do widget, será sempre esse o caso).
título: **``Indicar a etiqueta``**
mensagem: **``score=80 | video=1 | duration=20 | pre_capture=5``**
**pre_capture** (Frigate 0.18 ou superior): número de segundos gravados antes da criação do evento. Sem este parâmetro, o Frigate aplica a pré-gravação da câmara.
No que diz respeito à duração dos vídeos, é importante ter em conta que o Frigate adiciona tempo antes e depois do vídeo, 5 segundos por predefinição; assim, ao definir 20 segundos, obterá um vídeo de 30 segundos.
Tenha atenção aos eventos criados manualmente: se, na sua configuração do Frigate para **``record -> retain -> mode``**, tiver selecionado **``motion``**, os clips só estarão disponíveis se for detetado movimento; selecione **``all``** se quiser guardar tudo.

3. - **Filtrar eventos**
Mostrar apenas os eventos de uma ou mais câmaras, apenas de uma etiqueta ou de um tipo de evento, apenas os eventos da semana ou do ano em curso, etc...

# Criação de uma captura instantânea

Não quer criar um evento manualmente, mas quer obter uma captura instantânea da câmara? Pode criar uma ação na câmara que irá capturar a imagem da câmara.

Nas ações das câmaras encontram-se dois comandos:
- Capturar uma imagem (ação)
- URL da imagem (informações)

Cada captura é também registada como um evento com a etiqueta **captura**, visível na página Eventos. As capturas são eliminadas de acordo com as mesmas regras que os eventos (antiguidade e tamanho da pasta «data»): adicione aos favoritos aquelas que pretender manter.

O URL tem o formato **``/plugins/frigate/data/snapshots/id_snapshot.jpg``**, de modo a ser compatível com o maior número possível de plug-ins de comunicação.

Por exemplo, se pretender uma URL completa, pode definir o seguinte na configuração, cálculo e arredondamento do comando info:
**``str_replace('"','',"https://monjeedom.eu.jeedom.link"#value#)``**

Ou, para quem precisar do caminho:

**``str_replace('"','',"/var/www/html"#value#)``**

# <u>Configuração do Frigate</u>

**ATENÇÃO**: A alteração da configuração do servidor Frigate é da sua inteira responsabilidade! Não será prestado qualquer apoio técnico!

# <u>Logs Frigate</u>
Visualizar todos os registos do seu servidor Frigate

# <u>Cron</u>
**Se não utilizar o MQTT**: uma tarefa cron regular permite-lhe recuperar os eventos mais recentes e, assim, executar as ações associadas.

**Se utilizar o MQTT**: todos os novos eventos são recebidos automaticamente; basta uma tarefa cron horária ou diária: permite atualizar as informações do evento.

Em qualquer caso, mantenha pelo menos um cron ativo, pois será verificado sempre se os ficheiros guardados correspondem efetivamente a um evento e, caso contrário, serão eliminados.

O cronDaily é o único que verifica a versão do seu servidor Frigate: se houver uma atualização disponível, receberá uma mensagem.

**O meu conselho:**
Sem MQTT: cron ou cron5 (dependendo da capacidade do equipamento) + cronDaily
Com MQTT: cronDaily

***Em qualquer caso, se um cron estiver a ser executado, o seguinte não será iniciado e, no MQTT, os crons (1, 5, 10 e 15) estão desativados.***

# <u>Widget</u>
Aqui encontrará a imagem da câmara e os botões assinalados visíveis:
- um clique na imagem abre uma janela ampliada com as ações, os comandos PTZ e as predefinições;
- os botões de gravação, instantâneos, deteção, áudio e movimento, criação de eventos e captura;
- a lista suspensa de ações reúne as predefinições PTZ e os comandos HTTP visíveis;
- o ícone da chave inglesa abre o painel de IA: ativação da câmara, alertas e deteção de atividades, descrições geradas por IA generativa;
- um ícone apresenta os eventos da câmara na página «Eventos»;
- os ícones dos objetos detetados neste momento, para os comandos **Detecção xxx** visíveis.

Um botão só aparece se os seus comandos estiverem visíveis.

# <u>Fluxo de vídeo</u>
### configuração
No plugin Frigate **não existe um reprodutor para o fluxo de vídeo**; esta configuração destina-se aos plugins compatíveis.

O URL do fluxo de vídeo gravado no plugin é o do seu servidor Frigate e não o da câmara.

1. **Fluxo RTSP do Frigate**:
   - **Vantagens**: O Frigate permite centralizar os fluxos de várias câmaras, o que reduz o número de ligações diretas a cada câmara. Isto pode melhorar a estabilidade e a gestão dos recursos de rede.
   - **Desvantagens**: A configuração pode ser mais complexa, sobretudo se tiver várias câmaras com parâmetros diferentes.

2. **Fluxo RTSP da câmara**:
   - **Vantagens**: Utilizar diretamente o fluxo RTSP da câmara pode ser mais simples de configurar, especialmente se tiver apenas uma câmara ou se não quiser utilizar software intermediário.
   - **Desvantagens**: Cada dispositivo irá ligar-se diretamente à câmara, o que pode aumentar a carga na rede e na própria câmara.

Em resumo, se tiver várias câmaras e pretender uma gestão centralizada, o fluxo RTSP da Frigate poderá ser mais vantajoso. Se preferir uma solução mais simples e direta, utilizar o fluxo RTSP da câmara poderá ser suficiente.

### Com o JeeMate
Se a sua configuração do Frigate incluir vários fluxos por câmara, terá de indicar no campo «fluxo de vídeo» do seu equipamento aquele que pretende utilizar; o mesmo se aplica caso prefira utilizar o fluxo original da câmara.

Configuração do Frigate com um único fluxo; neste caso, não preciso de indicar o fluxo, pois o predefinido será suficiente.

```yaml
frigate1:
  ffmpeg:
    inputs:
      - path: rtsp://127.0.0.1:8554/frigate1
```

Configuração do Frigate com vários fluxos: indique o URL do fluxo pretendido na página do seu equipamento; o URL predefinido não será adequado; substitua 127.0.0.1 pelo endereço IP do servidor Frigate.

```yaml
    ffmpeg:
      inputs:
        - path: rtsp://127.0.0.1:8554/frigate1_high  # Flux principal haute résolution
          input_args: preset-rtsp-restream
          roles:
            - record        # Utilisé pour l’enregistrement
        - path: rtsp://127.0.0.1:8554/frigate1_low   # Flux secondaire basse résolution
          input_args: preset-rtsp-restream
          roles:
            - detect  
```

***Atenção: em caso algum lhe será pedido que altere a configuração no Frigate***

Após cada alteração do URL do fluxo no plugin Frigate, terá de guardar também no plugin JeeMate e, em seguida, efetuar uma sincronização completa na aplicação.

# <u>Painel</u>
Não se esqueça de ativar a página «Painel» nas definições gerais e, em seguida, de assinalar a caixa «Painel» em cada câmara.

- visualização das câmaras.
- página de eventos

# <u> Perguntas frequentes </u>

### O plugin está bem configurado em MQTT, mas não é executada nenhuma ação
O tópico «frigate/reviews» corresponde aos itens de revisão (períodos de atividade detetada) que são gerados após a deteção e o registo dos objetos. Este sistema de revisão depende fortemente da função de registo (recording) para funcionar:

O Frigate organiza os itens de revisão como intervalos de tempo que agrupam várias deteções

Se a gravação estiver desativada (record.enabled: false), nenhum segmento de vídeo é armazenado e, por isso, a plataforma não cria itens de revisão → nada é publicado em frigate/reviews.

Para funcionar, o plugin necessita, portanto, de:

```yaml
record:
  enabled: true
```

### Exemplo de ficheiro de configuração
Tenha em atenção que este é o meu ficheiro, as minhas configurações e que funciona na minha situação; cabe-lhe a si adaptá-lo ou compará-lo com o seu, caso alguma das funcionalidades do plugin não esteja a funcionar no seu caso.

Não me responsabilizo por qualquer avaria causada por esta configuração; por isso, deve adaptar a configuração ao seu próprio servidor e às suas necessidades.

Deixei alguns comentários para vos ajudar.

 ```yaml
 # Rappel, le plugin mqtt-manager nécéssite un broker mqtt sécurisé.
 mqtt:
  host: 192.168.2.22        # Adresse IP de votre serveur MQTT
  port: 1883                # Port du broker MQTT (1883 = standard non sécurisé)
  user: ***                 # Nom d'utilisateur (masqué ici)
  password: ***             # Mot de passe (masqué ici)
  stats_interval: 300       # Fréquence (en secondes) des messages de statistiques MQTT

detectors:
  coral:
    type: edgetpu           # Utilise un accélérateur Coral (Edge TPU) pour la détection
    device: usb             # Type de connexion : USB

ffmpeg:
  hwaccel_args: preset-intel-qsv-h264  # Accélération matérielle Intel Quick Sync pour le décodage vidéo

timestamp_style:
  position: tr              # Position du timestamp sur l’image (tr = top-right = coin supérieur droit)
  format: '%d/%m/%Y %H:%M:%S'  # Format du timestamp affiché (jour/mois/année heure:min:sec)

birdseye:
  enabled: false            # Désactive le mode Birdseye (vue multi-caméras combinée)

model:
  labelmap:                 # Remappage des classes de détection vers des noms personnalisés
    0: personne
    1: vehicule
    2: vehicule
    3: vehicule
    5: vehicule
    7: vehicule
    16: animale
    17: animale
    18: animale
    19: animale
    20: animale

detect:
  enabled: true             # Active globalement la détection d'objets pour toutes les caméras (ajouté automatiquement par frigate 0.16)

snapshots:
  enabled: true             # Active les captures d’image (snapshots) lors des événements
  clean_copy: true          # Génère une version sans annotation (utile pour archivage ou IA)
  timestamp: false          # Ne superpose pas la date/heure sur les images
  bounding_box: false       # Ne dessine pas de boîte de détection sur les images
  crop: false               # Ne recadre pas automatiquement l’objet détecté
  retain:                   # Durée de conservation des images
    default: 3              # Par défaut, conserve les snapshots 3 jours
    objects:
      personne: 7           # Conserve ceux contenant une "personne" pendant 7 jours
      vehicule: 3           # Conserve ceux contenant un "vehicule" pendant 3 jours

record:
  enabled: true             # Active l’enregistrement vidéo, obligatoire pour que le plugin reçoive les événements.
  retain:
    days: 1                 # Conserve les enregistrements pendant 1 jour
    mode: all               # Enregistre tout, même sans détection
  alerts:
    retain:
      days: 7               # Conserve les clips d’alerte pendant 7 jours
      mode: active_objects  # Seulement si un objet actif a été détecté
    pre_capture: 5          # Enregistre 5 secondes avant le début de l’événement
    post_capture: 5         # Enregistre 5 secondes après la fin de l’événement
  detections:
    retain:
      days: 7               # Conserve les clips avec détection pendant 7 jours
      mode: active_objects
    pre_capture: 3
    post_capture: 5

semantic_search:
  enabled: true             # Active l’analyse sémantique des événements (IA)
  reindex: false            # Ne re-analyse pas les anciens événements au démarrage

genai:
  enabled: true             # Active l’intégration IA (Google Gemini ici)
  provider: gemini          # Fournisseur de l’IA
  api_key: ***              # Clé API Gemini (masquée ici)
  model: gemini-1.5-flash   # Modèle utilisé pour l’analyse comportementale
  object_prompts:          # Prompts personnalisés pour chaque type d’objet, a vous de l'adapter si besoin.
    personne: >
      Commence IMMÉDIATEMENT et DIRECTEMENT la description de l'action...
    vehicule: >
      Décris IMMÉDIATEMENT et DIRECTEMENT le comportement du véhicule...
    animale: >
      Analyse IMMÉDIATEMENT et DIRECTEMENT le comportement de l'animal...

cameras:
  frigate1:                 # Nom de la caméra
    detect:
      fps: 5                # Taux d’analyse des images pour la détection
      enabled: true         # Active la détection pour cette caméra
      width: 640            # Largeur du flux vidéo analysé
      height: 360           # Hauteur du flux vidéo analysé
      stationary:
        interval: 50        # Vérifie les objets immobiles toutes les 50 frames
        threshold: 30       # Seuil de mouvement à partir duquel un objet est considéré comme "mobile"
    ffmpeg:
      inputs:
        - path: rtsp://127.0.0.1:8554/frigate1_high  # Flux principal haute résolution
          input_args: preset-rtsp-restream
          roles:
            - record        # Utilisé pour l’enregistrement
        - path: rtsp://127.0.0.1:8554/frigate1_low   # Flux secondaire basse résolution
          input_args: preset-rtsp-restream
          roles:
            - detect        # Utilisé pour la détection
    objects:
      track:                # Liste des objets à détecter
        - personne
        - vehicule
        - animale
      filters:              # Filtres pour chaque type d’objet
        personne:
          min_score: 0.65   # Score minimum pour commencer à suivre
          threshold: 0.7    # Score minimum pour déclencher un événement
        vehicule:
          min_score: 0.7
          threshold: 0.8
        animale:
          min_score: 0.7
          threshold: 0.8

go2rtc:
  streams:                  # Flux vidéo déclarés pour usage interne (re-streaming)
    frigate1_low: rtsp://***:***@192.168.2.36:554/2   # Flux basse qualité
    frigate1_high: rtsp://***:***@192.168.2.36:554/1  # Flux haute qualité

version: 0.16-0             # Version utilisée de Frigate
```


