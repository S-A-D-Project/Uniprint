(function(){
'use strict';

const csrfToken=()=>{const el=document.querySelector('meta[name="csrf-token"]');return el?el.content:''};

async function apiFetch(url,options){
  const opts=options||{};
  const headers=new Headers(opts.headers||{});
  if(!headers.has('Accept')) headers.set('Accept','application/json');
  if(!headers.has('X-Requested-With')) headers.set('X-Requested-With','XMLHttpRequest');
  if(!headers.has('X-CSRF-TOKEN')) headers.set('X-CSRF-TOKEN',csrfToken());
  opts.headers=headers;
  const res=await fetch(url,opts);
  const isJson=(res.headers.get('content-type')||'').includes('application/json');
  const data=isJson?await res.json():await res.text();
  if(!res.ok){
    const msg=(data&&data.message)?data.message:(typeof data==='string'?data:'Request failed');
    throw new Error(msg);
  }
  return data;
}

function el(id){return document.getElementById(id)}

const state={
  conversations:[],
  currentConversationId:null,
  pusher:null,
  channel:null,
  typingTimer:null,
};

function formatTime(iso){
  if(!iso) return '';
  const d=new Date(iso);
  return d.toLocaleString(undefined,{hour:'2-digit',minute:'2-digit'});
}

function setConnectionStatus(kind,text){
  const cs=el('connectionStatus');
  if(!cs) return;
  cs.className='connection-status '+kind;
  cs.innerHTML='<i class="bi bi-wifi"></i> '+(text||'');
}

function renderConversations(){
  const list=el('conversationsList');
  if(!list) return;
  if(!state.conversations.length){
    list.innerHTML='<div class="text-center py-4 text-muted">No conversations yet.</div>';
    return;
  }

  list.innerHTML='';
  state.conversations.forEach(c=>{
    const btn=document.createElement('button');
    btn.type='button';
    btn.className='w-100 text-start border-0 bg-transparent';
    btn.style.padding='0';

    const active=state.currentConversationId===c.conversation_id;
    const unread=c.unread_count>0;

    btn.innerHTML=`
      <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="cursor:pointer;${active?'background:#f8fafc;':''}">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:white;font-weight:700;flex:0 0 auto;">
          ${(c.participant?.name||'?').slice(0,2).toUpperCase()}
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-bold text-truncate">${c.participant?.name||'Conversation'}</div>
            <small class="text-muted">${formatTime(c.last_message_at)}</small>
          </div>
          <div class="d-flex justify-content-between align-items-center gap-2">
            <small class="text-muted text-truncate">${c.last_message?.message_text||'No messages yet'}</small>
            ${unread?`<span class="badge bg-primary">${c.unread_count}</span>`:''}
          </div>
        </div>
      </div>`;

    btn.addEventListener('click',()=>openConversation(c.conversation_id,c.participant));
    list.appendChild(btn);
  });
}

function renderMessage(m){
  const wrap=el('chatMessages');
  if(!wrap) return;

  const userId=window.Laravel?.user?.id;
  const isSelf=(m.sender_id||m.senderId||m.sender?.user_id)===userId;

  const row=document.createElement('div');
  row.className='message-row'+(isSelf?' self':'');

  const text=m.message_text||m.messageText||m.text||'';
  const createdAt=m.created_at||m.createdAt||m.timestamp||new Date().toISOString();

  row.innerHTML=`
    <div>
      <div class="message-bubble">${escapeHtml(text).replace(/\n/g,'<br>')}</div>
      <div class="message-meta">${formatTime(createdAt)}</div>
    </div>`;

  wrap.appendChild(row);
  wrap.scrollTop=wrap.scrollHeight;
}

function renderMessages(messages){
  const wrap=el('chatMessages');
  if(!wrap) return;
  wrap.innerHTML='';
  (messages||[]).forEach(renderMessage);
}

function escapeHtml(str){
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#039;');
}

function setActiveChatVisible(visible){
  const empty=el('emptyState');
  const active=el('activeChat');
  if(empty) empty.style.display=visible?'none':'block';
  if(active) active.style.display=visible?'flex':'none';
}

function updateHeader(participant){
  const name=participant?.name||'Conversation';
  const avatar=el('participantAvatar');
  const label=el('participantName');
  if(label) label.textContent=name;
  if(avatar) avatar.textContent=name.slice(0,2).toUpperCase();
}

function setupComposer(){
  const sendBtn=el('sendButton');
  const input=el('messageInput');
  if(!sendBtn||!input) return;

  const send=async()=>{
    const text=input.value.trim();
    if(!text||!state.currentConversationId) return;

    const nowIso=new Date().toISOString();
    const temp={
      sender_id: window.Laravel?.user?.id,
      sender_name: window.Laravel?.user?.name,
      message_text: text,
      message_type: 'text',
      created_at: nowIso,
    };

    input.value='';
    renderMessage(temp);

    try{
      await apiFetch('/api/chat/messages',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
          conversation_id: state.currentConversationId,
          message_text: text,
          message_type: 'text',
          sender_id: window.Laravel?.user?.id,
          sender_name: window.Laravel?.user?.name,
          message_id: crypto.randomUUID?crypto.randomUUID():String(Date.now()),
          timestamp: nowIso,
        })
      });
    }catch(e){
      console.error('[Chat] send failed',e);
    }
  };

  sendBtn.addEventListener('click',send);
  input.addEventListener('keydown',(e)=>{
    if(e.key==='Enter'&&!e.shiftKey){
      e.preventDefault();
      send();
    }
  });

  input.addEventListener('input',()=>{
    if(!state.currentConversationId) return;
    if(state.typingTimer) window.clearTimeout(state.typingTimer);
    state.typingTimer=window.setTimeout(()=>{
      apiFetch('/api/chat/typing',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({conversation_id: state.currentConversationId,is_typing:false})
      }).catch(()=>{});
    },1200);

    apiFetch('/api/chat/typing',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({conversation_id: state.currentConversationId,is_typing:true})
    }).catch(()=>{});
  });
}

function setupPusher(){
  const cfg=window.Laravel?.pusher;
  if(!cfg||!cfg.key) return;

  try{
    state.pusher=new Pusher(cfg.key,{
      cluster: cfg.cluster,
      forceTLS:true,
      authEndpoint:'/api/chat/pusher/auth',
      auth:{headers:{'X-CSRF-TOKEN': csrfToken()}},
    });

    state.pusher.connection.bind('connected',()=>setConnectionStatus('connected','Connected'));
    state.pusher.connection.bind('connecting',()=>setConnectionStatus('connecting','Connecting...'));
    state.pusher.connection.bind('disconnected',()=>setConnectionStatus('disconnected','Disconnected'));
    state.pusher.connection.bind('error',()=>setConnectionStatus('error','Connection error'));

  }catch(e){
    console.error('[Chat] Pusher init failed',e);
  }
}

function bindPusherToConversation(conversationId){
  if(!state.pusher) return;

  if(state.channel){
    try{ state.pusher.unsubscribe(state.channel.name); }catch(_){ }
    state.channel=null;
  }

  const channelName='conversation.'+conversationId;
  state.channel=state.pusher.subscribe(channelName);

  state.channel.bind('new-message',(data)=>{
    const userId=window.Laravel?.user?.id;
    if(data && data.sender_id===userId) return;
    renderMessage(data);
  });

  state.channel.bind('user-typing',(data)=>{
    const ind=document.querySelector('#activeChat #typingIndicator');
    if(!ind) return;
    if(data && data.user_id===window.Laravel?.user?.id) return;
    ind.style.display=data?.is_typing?'block':'none';
    if(data?.is_typing){
      ind.innerHTML='<i class="bi bi-three-dots"></i> '+escapeHtml(data.user_name||'Someone')+' is typing...';
    }
  });
}

async function openConversation(conversationId,participant){
  state.currentConversationId=conversationId;
  setActiveChatVisible(true);
  updateHeader(participant);
  renderConversations();
  bindPusherToConversation(conversationId);

  try{
    const data=await apiFetch('/api/chat/conversations/'+conversationId+'/messages');
    renderMessages(data.messages||[]);
  }catch(e){
    console.error('[Chat] load messages failed',e);
  }
}

async function loadConversations(){
  try{
    const data=await apiFetch('/api/chat/conversations');
    state.conversations=data.conversations||[];
    renderConversations();
  }catch(e){
    console.error('[Chat] load conversations failed',e);
  }
}

async function setupNewChat(){
  const btn=el('startNewChatBtn');
  if(!btn) return;

  btn.addEventListener('click',async()=>{
    try{
      const data=await apiFetch('/api/chat/available-businesses');
      const list=document.getElementById('businessList');
      if(!list) return;

      const businesses=data.businesses||[];
      list.innerHTML='';

      if(!businesses.length){
        list.innerHTML='<div class="text-center text-muted py-3">No businesses available.</div>';
      }else{
        businesses.forEach(b=>{
          const row=document.createElement('button');
          row.type='button';
          row.className='list-group-item list-group-item-action';
          row.innerHTML=`<div class="fw-bold">${escapeHtml(b.name||b.email||'Business')}</div><small class="text-muted">${escapeHtml(b.email||'')}</small>`;
          row.addEventListener('click',async()=>{
            try{
              const conv=await apiFetch('/api/chat/conversations',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({business_id: b.user_id})
              });

              const modalEl=document.getElementById('businessListModal');
              if(modalEl && window.bootstrap){
                const inst=window.bootstrap.Modal.getInstance(modalEl)||new window.bootstrap.Modal(modalEl);
                inst.hide();
              }

              await loadConversations();
              if(conv.conversation){
                openConversation(conv.conversation.conversation_id,conv.conversation.business);
              }
            }catch(err){
              console.error('[Chat] create conversation failed',err);
            }
          });
          list.appendChild(row);
        });
        list.classList.add('list-group');
      }

      const modalEl=document.getElementById('businessListModal');
      if(modalEl && window.bootstrap){
        const inst=window.bootstrap.Modal.getInstance(modalEl)||new window.bootstrap.Modal(modalEl);
        inst.show();
      }
    }catch(e){
      console.error('[Chat] load businesses failed',e);
    }
  });
}

document.addEventListener('DOMContentLoaded',()=>{
  setConnectionStatus('connecting','Connecting...');
  setupPusher();
  setupComposer();
  setupNewChat();
  loadConversations();
});

})();
