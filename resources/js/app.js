import './bootstrap';

const digits = value => value.replace(/\D/g, '');
const masks = {
    cpf: value => digits(value).slice(0, 11).replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'),
    cep: value => digits(value).slice(0, 8).replace(/(\d{5})(\d)/, '$1-$2'),
    phone: value => { const d=digits(value).slice(0,11); return d.length>10?d.replace(/(\d{2})(\d{5})(\d{0,4})/,'($1) $2-$3'):d.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3'); },
    placa: value => value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,7).replace(/^([A-Z]{3})([A-Z0-9])/,'$1-$2'),
    chassi: value => value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,50),
    renavam: value => digits(value).slice(0, 11),
    currency: value => {
        const raw = digits(value);
        if (!raw) return '';
        const amount = (Number(raw) / 100).toFixed(2).split('.');
        return `${Number(amount[0]).toLocaleString('pt-BR')},${amount[1]}`;
    },
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select').forEach(select => {
        select.addEventListener('pointerdown', event => {
            if (event.button !== 0 || select.disabled || typeof select.showPicker !== 'function') return;
            try {
                select.showPicker();
                event.preventDefault();
            } catch (_) {
                // O comportamento nativo permanece disponível em navegadores sem showPicker.
            }
        });
    });
    const catalogSystemSelect = document.getElementById('service-system-select');
    const systemCustomWrap = document.getElementById('service-system-custom-wrap');
    const systemCustom = document.getElementById('service-system-custom');
    if (catalogSystemSelect && systemCustomWrap && systemCustom) {
        const toggleCustomSystem = () => {
            const visible = catalogSystemSelect.value === 'outro';
            systemCustomWrap.hidden = !visible;
            systemCustom.required = visible;
            if (visible) systemCustom.focus();
        };
        catalogSystemSelect.addEventListener('change', toggleCustomSystem);
        toggleCustomSystem();
    }
    const applyTheme = theme => {
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('theme', theme);
        document.querySelectorAll('[data-theme-toggle]').forEach(button => {
            button.setAttribute('aria-label', theme === 'dark' ? 'Usar tema claro' : 'Usar tema escuro');
            const label = button.querySelector('.theme-label');
            const icon = button.querySelector('.theme-icon');
            if (label) label.textContent = theme === 'dark' ? 'Claro' : 'Escuro';
            if (icon) icon.textContent = theme === 'dark' ? '☀' : '☾';
        });
    };
    applyTheme(document.documentElement.dataset.theme || 'light');
    document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
        applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
    }));

    document.querySelector('[data-toggle-gallery-form]')?.addEventListener('click', event => {
        const form = document.querySelector('.alternative-gallery-form');
        if (! form) return;
        form.hidden = ! form.hidden;
        event.currentTarget.textContent = form.hidden ? '＋ Criar galeria' : 'Fechar';
        if (! form.hidden) form.querySelector('input')?.focus();
    });

    const usageFilter = document.querySelector('[data-usage-filter]');
    if (usageFilter) {
        const all = usageFilter.querySelector('[data-usage-all]');
        const types = [...usageFilter.querySelectorAll('input[name="tipos_uso[]"]')];
        const summary = usageFilter.querySelector('[data-usage-summary]');
        const refresh = () => {
            const checked = types.filter(input => input.checked);
            all.checked = checked.length === 0;
            summary.textContent = checked.length === 0 ? 'Todos os tipos' : checked.length === 1 ? checked[0].parentElement.textContent.trim() : `${checked.length} tipos selecionados`;
        };
        all.addEventListener('change', () => {
            if (all.checked) types.forEach(input => { input.checked = false; });
            refresh();
        });
        types.forEach(input => input.addEventListener('change', refresh));
        refresh();
    }

    document.querySelectorAll('.bulk-photo-form').forEach(form => {
        const inputs = [...document.querySelectorAll(`input[type="checkbox"][form="${form.id}"][name="fotos[]"]`)];
        const button = form.querySelector('[data-bulk-delete]');
        const count = form.querySelector('[data-bulk-count]');
        const selectAll = form.querySelector('[data-select-all]');
        const refresh = () => {
            const selected = inputs.filter(input => input.checked).length;
            if (count) count.textContent = selected;
            if (button) button.disabled = selected === 0;
            if (selectAll) {
                const allSelected = inputs.length > 0 && selected === inputs.length;
                selectAll.textContent = allSelected ? 'Desmarcar todas' : 'Selecionar todas';
                selectAll.setAttribute('aria-pressed', allSelected ? 'true' : 'false');
            }
            inputs.forEach(input => input.closest('figure')?.classList.toggle('selected', input.checked));
        };
        selectAll?.addEventListener('click', () => {
            const shouldSelect = inputs.some(input => !input.checked);
            inputs.forEach(input => { input.checked = shouldSelect; });
            refresh();
        });
        inputs.forEach(input => input.addEventListener('change', refresh));
        refresh();
    });

    const galleryPanel = document.querySelector('.gallery-panel');
    if (galleryPanel) galleryPanel.id = 'vehicle-photo-upload';
    const galleryForm=document.querySelector('.gallery-form');
    if(galleryForm){
        const token=galleryForm.querySelector('input[name=_token]')?.value||'';
        galleryForm.innerHTML=`<input type="hidden" name="_token" value="${token}"><label class="gallery-dropzone"><input type="file" name="fotos[]" accept="image/*" multiple><span class="gallery-drop-icon">＋</span><strong>Arraste as fotos para cá</strong><small>ou clique para escolher · várias fotos de uma vez</small></label>`;
        const dropzone=galleryForm.querySelector('.gallery-dropzone');
        const input=dropzone.querySelector('input');
        const send=()=>{if(!input.files?.length)return;dropzone.classList.add('uploading');dropzone.querySelector('strong').textContent=`Enviando ${input.files.length} foto${input.files.length>1?'s':''}...`;galleryForm.submit();};
        input.addEventListener('change',send);
        ['dragenter','dragover'].forEach(event=>dropzone.addEventListener(event,e=>{e.preventDefault();dropzone.classList.add('dragging');}));
        ['dragleave','drop'].forEach(event=>dropzone.addEventListener(event,e=>{e.preventDefault();dropzone.classList.remove('dragging');}));
        dropzone.addEventListener('drop',e=>{if(!e.dataTransfer.files.length)return;const transfer=new DataTransfer();[...e.dataTransfer.files].filter(file=>file.type.startsWith('image/')).forEach(file=>transfer.items.add(file));input.files=transfer.files;send();});
    }
    document.querySelectorAll('.photo-drop-field').forEach(zone=>{
        const input=zone.querySelector('input[type=file]');
        const block=zone.closest('.maintenance-upload-block');
        const status=block?.querySelector('[data-photo-status]');
        const previews=block?.querySelector('[data-photo-previews]');
        if(!input)return;
        const render=()=>{
            const files=[...(input.files||[])];
            if(status)status.textContent=files.length?`${files.length} foto${files.length>1?'s':''} pronta${files.length>1?'s':''} para salvar`:'Nenhuma foto selecionada';
            if(!previews)return;
            previews.replaceChildren();previews.hidden=!files.length;
            files.forEach((file,index)=>{
                const card=document.createElement('figure');
                const image=document.createElement('img');image.src=URL.createObjectURL(file);image.alt='Prévia de '+file.name;image.onload=()=>URL.revokeObjectURL(image.src);
                const caption=document.createElement('figcaption');
                const name=document.createElement('span');name.textContent=file.name;
                const remove=document.createElement('button');remove.type='button';remove.textContent='×';remove.setAttribute('aria-label','Remover '+file.name);
                remove.addEventListener('click',()=>{const transfer=new DataTransfer();files.filter((_,itemIndex)=>itemIndex!==index).forEach(item=>transfer.items.add(item));input.files=transfer.files;render();});
                caption.append(name,remove);card.append(image,caption);previews.append(card);
            });
        };
        input.addEventListener('change',render);
        ['dragenter','dragover'].forEach(event=>zone.addEventListener(event,e=>{e.preventDefault();zone.classList.add('dragging');}));
        ['dragleave','drop'].forEach(event=>zone.addEventListener(event,e=>{e.preventDefault();zone.classList.remove('dragging');}));
        zone.addEventListener('drop',e=>{const transfer=new DataTransfer();[...e.dataTransfer.files].filter(file=>file.type.startsWith('image/')).forEach(file=>transfer.items.add(file));input.files=transfer.files;input.dispatchEvent(new Event('change',{bubbles:true}));});
    });
    const vehicleGallery=document.getElementById('vehicle-gallery-modal');
    if(vehicleGallery){
        const heroActions=document.querySelector('.vehicle-hero .actions');
        const slides=[...vehicleGallery.querySelectorAll('[data-gallery-slide]')];
        const thumbs=[...vehicleGallery.querySelectorAll('[data-gallery-thumb]')];
        const counter=vehicleGallery.querySelector('[data-gallery-counter]');
        let current=0;
        const show=index=>{current=(index+slides.length)%slides.length;slides.forEach((slide,i)=>slide.classList.toggle('active',i===current));thumbs.forEach((thumb,i)=>thumb.classList.toggle('active',i===current));if(counter)counter.textContent=`${current+1} de ${slides.length}`;};
        const open=index=>{show(index);vehicleGallery.showModal();};
        if(heroActions){const openButton=document.createElement('button');openButton.type='button';openButton.className='btn gallery-open-button';openButton.textContent='▣ Ver galeria';heroActions.prepend(openButton);openButton.addEventListener('click',()=>open(0));}
        document.querySelectorAll('[data-gallery-open]').forEach(button=>button.addEventListener('click',()=>open(0)));
        document.querySelectorAll('[data-gallery-open-index]').forEach(button=>button.addEventListener('click',()=>open(Number(button.dataset.galleryOpenIndex)||0)));
        vehicleGallery.querySelector('[data-gallery-close]')?.addEventListener('click',()=>vehicleGallery.close());
        vehicleGallery.querySelector('[data-gallery-prev]')?.addEventListener('click',()=>show(current-1));
        vehicleGallery.querySelector('[data-gallery-next]')?.addEventListener('click',()=>show(current+1));
        thumbs.forEach((thumb,index)=>thumb.addEventListener('click',()=>show(index)));
        vehicleGallery.addEventListener('click',event=>{if(event.target===vehicleGallery)vehicleGallery.close();});
        vehicleGallery.addEventListener('keydown',event=>{if(event.key==='ArrowLeft'){event.preventDefault();show(current-1);}if(event.key==='ArrowRight'){event.preventDefault();show(current+1);}});
        vehicleGallery.querySelectorAll('[data-framing-toggle]').forEach(button=>button.addEventListener('click',()=>{const form=button.nextElementSibling;form.hidden=!form.hidden;button.textContent=form.hidden?'Editar enquadramento do destaque':'Fechar ajuste';}));
        vehicleGallery.querySelectorAll('.framing-form').forEach(form=>{const previews=form.querySelectorAll('.framing-preview img');const x=form.querySelector('[name=posicao_x]');const y=form.querySelector('[name=posicao_y]');const refresh=()=>previews.forEach(preview=>preview.style.objectPosition=`${x.value}% ${y.value}%`);x.addEventListener('input',refresh);y.addEventListener('input',refresh);refresh();});
        show(0);
        if(new URLSearchParams(window.location.search).get('visualizar')==='galeria') open(0);
    }
    document.querySelectorAll('[data-photo-dialog]').forEach(button => button.addEventListener('click', () => {
        document.getElementById(button.dataset.photoDialog)?.showModal();
    }));
    document.querySelectorAll('.photo-picker input[type=file]').forEach(input => input.addEventListener('change', () => {
        const preview = input.closest('.photo-picker')?.querySelector('[data-photo-preview]');
        const file = input.files?.[0];
        if (! preview || ! file) return;
        preview.style.backgroundImage = `linear-gradient(#0003,#0003), url('${URL.createObjectURL(file)}')`;
        preview.classList.add('has-photo');
        preview.querySelector('strong').textContent = file.name;
        preview.querySelector('small').textContent = 'Clique para trocar a imagem';
    }));
    document.querySelectorAll('.document-field input[type=file]').forEach(input => input.addEventListener('change', () => {
        const file=input.files?.[0];
        if(!file)return;
        const field=input.closest('.document-field');
        const status=field.querySelector('[data-file-status]');
        const preview=field.querySelector('[data-file-preview]');
        const size=file.size<1048576?`${Math.max(1,Math.round(file.size/1024))} KB`:`${(file.size/1048576).toFixed(1)} MB`;
        if(status)status.textContent=`✓ Arquivo carregado: ${file.name} · ${size}`;
        if(preview){preview.classList.add('loaded');preview.textContent=file.type.startsWith('image/')?'▧':'PDF';}
        field.classList.add('has-file');
    }));
    document.querySelectorAll('.service-attachment-field input[type=file]').forEach(input => {
        const zone=input.closest('.service-attachment-field');
        const render=()=>{
        const block=input.closest('.maintenance-upload-block');
        const status=block?.querySelector('[data-file-status]');
        const list=block?.querySelector('[data-attachment-list]');
        const files=[...(input.files||[])];
        if(status)status.textContent=files.length?`${files.length} anexo${files.length>1?'s':''} pronto${files.length>1?'s':''} para salvar`:'Nenhum anexo selecionado';
        input.closest('.service-attachment-field')?.classList.toggle('has-file',files.length>0);
        if(!list)return;
        list.replaceChildren();list.hidden=!files.length;
        files.forEach((file,index)=>{
            const row=document.createElement('div');
            const icon=document.createElement('span');icon.className='pending-file-icon';icon.textContent=file.type==='application/pdf'?'PDF':'IMG';
            const details=document.createElement('div');const name=document.createElement('strong');name.textContent=file.name;const size=document.createElement('small');size.textContent=file.size<1048576?`${Math.max(1,Math.round(file.size/1024))} KB`:`${(file.size/1048576).toFixed(1)} MB`;details.append(name,size);
            const remove=document.createElement('button');remove.type='button';remove.textContent='×';remove.setAttribute('aria-label','Remover '+file.name);remove.addEventListener('click',()=>{const transfer=new DataTransfer();files.filter((_,itemIndex)=>itemIndex!==index).forEach(item=>transfer.items.add(item));input.files=transfer.files;input.dispatchEvent(new Event('change',{bubbles:true}));});
            row.append(icon,details,remove);list.append(row);
        });
        };
        input.addEventListener('change',render);
        ['dragenter','dragover'].forEach(eventName=>zone?.addEventListener(eventName,event=>{event.preventDefault();event.stopPropagation();zone.classList.add('dragging');}));
        ['dragleave','drop'].forEach(eventName=>zone?.addEventListener(eventName,event=>{event.preventDefault();event.stopPropagation();zone.classList.remove('dragging');}));
        zone?.addEventListener('drop',event=>{
            const accepted=['application/pdf','image/jpeg','image/png','image/webp'];
            const transfer=new DataTransfer();
            [...event.dataTransfer.files].filter(file=>accepted.includes(file.type)).forEach(file=>transfer.items.add(file));
            input.files=transfer.files;
            input.dispatchEvent(new Event('change',{bubbles:true}));
        });
    });
    const providerSelect=document.getElementById('maintenance-provider');
    const providerFields=document.getElementById('provider-inline-fields');
    if(providerSelect&&providerFields){
        const providerName=providerFields.querySelector('[name=prestador_nome]');
        const toggleProvider=()=>{const custom=providerSelect.value==='outro';providerFields.hidden=!custom;if(providerName)providerName.required=custom;};
        providerSelect.addEventListener('change',toggleProvider);toggleProvider();
    }
    let syncBreakdownServices=()=>{};
    let serviceOptions=[...document.querySelectorAll('[data-service-option]')];
    const serviceCount=document.querySelector('[data-service-count]');
    if(serviceOptions.length){
        const picker=document.querySelector('[data-service-picker]');
        const select=picker?.querySelector('[data-service-select]');
        const empty=picker?.querySelector('.selected-services-empty');
        const specBox=document.querySelector('[data-maintenance-specs]');
        const vehicleSelect=document.getElementById('maintenance-vehicle');
        const quickForm=picker?.querySelector('[data-quick-service]');
        const quickSystem=picker?.querySelector('[data-new-service-system]');
        const quickName=picker?.querySelector('[data-new-service-name]');
        const quickError=picker?.querySelector('[data-new-service-error]');
        const refreshSpecs=()=>{if(!specBox)return;const ids=serviceOptions.filter(option=>option.querySelector('input').checked).map(option=>option.dataset.serviceId);let visible=0;specBox.querySelectorAll('[data-spec-service]').forEach(item=>{const show=item.dataset.specVehicle===vehicleSelect?.value&&ids.includes(item.dataset.specService);item.hidden=!show;if(show)visible++;});specBox.hidden=visible===0;};
        const updateCount=()=>{const count=serviceOptions.filter(option=>option.querySelector('input').checked).length;if(serviceCount)serviceCount.textContent=`${count} selecionado${count===1?'':'s'}`;if(empty)empty.hidden=count>0;refreshSpecs();syncBreakdownServices();};
        vehicleSelect?.addEventListener('change',refreshSpecs);
        const bindRemove=option=>option.querySelector('[data-service-remove]')?.addEventListener('click',()=>{const input=option.querySelector('input');input.checked=false;option.hidden=true;const selectOption=[...select.options].find(item=>item.value===input.value);if(selectOption)selectOption.disabled=false;updateCount();});
        select?.addEventListener('change',()=>{if(select.value==='outro'){quickForm.hidden=false;quickName.focus();select.value='';return;}const selected=select.selectedOptions[0];const option=serviceOptions.find(item=>item.dataset.serviceId===select.value);if(!option)return;option.hidden=false;option.querySelector('input').checked=true;selected.disabled=true;select.value='';updateCount();});
        serviceOptions.forEach(bindRemove);
        picker?.querySelector('[data-cancel-new-service]')?.addEventListener('click',()=>{quickForm.hidden=true;quickName.value='';quickError.hidden=true;});
        picker?.querySelector('[data-save-new-service]')?.addEventListener('click',async event=>{
            const button=event.currentTarget;const nome=quickName.value.trim();
            if(!nome){quickError.textContent='Informe o nome do serviço.';quickError.hidden=false;quickName.focus();return;}
            button.disabled=true;quickError.hidden=true;
            try{
                const response=await fetch(picker.dataset.createUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('input[name="_token"]')?.value||''},body:JSON.stringify({sistema_veiculo_id:quickSystem.value,nome})});
                const data=await response.json();
                if(!response.ok)throw new Error(data.message||Object.values(data.errors||{}).flat()[0]||'Não foi possível cadastrar.');
                let option=serviceOptions.find(item=>item.dataset.serviceId===String(data.id));
                if(!option){option=document.createElement('label');option.dataset.serviceOption='';option.dataset.serviceId=data.id;option.dataset.serviceSystem=data.sistema;option.dataset.serviceName=data.nome;option.innerHTML=`<input type="checkbox" name="tipo_manutencao_ids[]" value="${data.id}" checked><span><small></small><strong></strong></span><button type="button" data-service-remove aria-label="Remover">×</button>`;option.querySelector('small').textContent=data.sistema;option.querySelector('strong').textContent=data.nome;picker.querySelector('.selected-services-empty').before(option);serviceOptions.push(option);bindRemove(option);const group=[...select.querySelectorAll('optgroup')].find(item=>item.label===data.sistema);group?.appendChild(new Option(data.nome,data.id,false,false));}
                option.hidden=false;option.querySelector('input').checked=true;const selectOption=[...select.options].find(item=>item.value===String(data.id));if(selectOption)selectOption.disabled=true;
                quickForm.hidden=true;quickName.value='';updateCount();
            }catch(error){quickError.textContent=error.message;quickError.hidden=false;}finally{button.disabled=false;}
        });
        updateCount();
    }
    const breakdownRows=document.getElementById('breakdown-rows');
    const breakdownTemplate=document.getElementById('breakdown-row-template');
    const addBreakdown=document.getElementById('add-breakdown-row');
    const maintenanceTotal=document.getElementById('maintenance-total');
    if(breakdownRows&&breakdownTemplate&&addBreakdown){
        if(maintenanceTotal?.dataset.mask==='currency')maintenanceTotal.value=masks.currency(maintenanceTotal.value);
        let nextBreakdownIndex=breakdownRows.querySelectorAll('[data-breakdown-row]').length;
        const numberFromBR=value=>Number(String(value||'0').replace(/\./g,'').replace(',','.'))||0;
        syncBreakdownServices=()=>{
            const selected=serviceOptions.filter(item=>item.querySelector('input')?.checked);
            breakdownRows.querySelectorAll('[name$="[tipo_manutencao_id]"]').forEach(field=>{
                const current=field.value;
                const groups=new Map();
                selected.forEach(item=>{
                    const system=item.dataset.serviceSystem||'Serviços';
                    if(!groups.has(system))groups.set(system,[]);
                    groups.get(system).push({id:item.dataset.serviceId,name:item.dataset.serviceName});
                });
                field.replaceChildren(new Option('Relacionar ao serviço...',''));
                groups.forEach((services,system)=>{
                    const group=document.createElement('optgroup');group.label=system;
                    services.forEach(service=>group.appendChild(new Option(service.name,service.id,false,service.id===current)));
                    field.appendChild(group);
                });
                if(!selected.some(item=>item.dataset.serviceId===current))field.value='';
            });
        };
        const refreshBreakdown=()=>{
            const sum=[...breakdownRows.querySelectorAll('[data-breakdown-row]')].reduce((total,row)=>total+(Number(row.querySelector('[name$="[quantidade]"]')?.value)||0)*numberFromBR(row.querySelector('[name$="[valor_unitario]"]')?.value),0);
            const sumNode=document.querySelector('[data-breakdown-sum]');
            const warning=document.querySelector('[data-breakdown-warning]');
            if(sumNode)sumNode.textContent=sum.toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
            const total=numberFromBR(maintenanceTotal?.value);
            if(warning){warning.className='breakdown-ok';warning.textContent=`Total do registro: ${(total+sum).toLocaleString('pt-BR',{style:'currency',currency:'BRL'})}`;}
        };
        const setupRow=row=>{
            const currency=row.querySelector('.item-currency');
            const category=row.querySelector('[name$="[categoria]"]');
            const supplier=row.querySelector('[name$="[fornecedor_id]"]');
            const supplierFields=row.querySelector('.breakdown-supplier');
            const supplierName=row.querySelector('[name$="[fornecedor_nome]"]');
            const syncSupplier=()=>{
                if(supplierFields)supplierFields.hidden=category?.value==='servico';
                if(supplierName)supplierName.hidden=supplier?.value!=='outro'||category?.value==='servico';
            };
            if(currency){const apply=()=>{currency.value=masks.currency(currency.value);refreshBreakdown();};currency.addEventListener('input',apply);apply();}
            category?.addEventListener('change',syncSupplier);
            supplier?.addEventListener('change',syncSupplier);
            row.querySelector('[name$="[quantidade]"]')?.addEventListener('input',refreshBreakdown);
            row.querySelector('[data-remove-breakdown]')?.addEventListener('click',()=>{row.remove();refreshBreakdown();});
            syncSupplier();
        };
        breakdownRows.querySelectorAll('[data-breakdown-row]').forEach(setupRow);syncBreakdownServices();
        addBreakdown.addEventListener('click',()=>{const fragment=breakdownTemplate.content.cloneNode(true);const row=fragment.querySelector('[data-breakdown-row]');row.querySelectorAll('[data-name]').forEach(field=>{field.name=`itens[${nextBreakdownIndex}][${field.dataset.name}]`;field.removeAttribute('data-name');});nextBreakdownIndex++;breakdownRows.appendChild(fragment);setupRow(breakdownRows.lastElementChild);syncBreakdownServices();refreshBreakdown();});
        maintenanceTotal?.addEventListener('input',refreshBreakdown);refreshBreakdown();
    }
    const openFormTab = name => {
        document.querySelectorAll('[data-form-tab]').forEach(tab => tab.classList.toggle('active', tab.dataset.formTab === name));
        document.querySelectorAll('[data-form-panel]').forEach(panel => panel.classList.toggle('active', panel.dataset.formPanel === name));
    };
    document.querySelectorAll('[data-form-tab]').forEach(tab => tab.addEventListener('click', () => openFormTab(tab.dataset.formTab)));
    document.querySelectorAll('[data-next-tab]').forEach(button => button.addEventListener('click', () => openFormTab(button.dataset.nextTab)));

    document.querySelectorAll('[data-mask]').forEach(input => {
        const apply=()=>{input.value=masks[input.dataset.mask](input.value)};
        input.addEventListener('input',apply); apply();
    });

    const marca=document.getElementById('marca_veiculo_id');
    const modelo=document.getElementById('modelo_veiculo_id');
    if (marca && modelo) {
        const marcaCustom=document.getElementById('marca_custom');
        const modeloCustom=document.getElementById('modelo_custom');
        const selected=String(modelo.dataset.selected||'');
        const refresh=()=>{
            const outra=marca.value==='outra';
            marcaCustom.hidden=!outra; marcaCustom.required=outra;
            modelo.innerHTML='<option value="">Selecione o modelo</option>';
            (window.catalogoVeiculos?.[marca.value]||[]).forEach(item=>modelo.add(new Option(item.nome,item.id,false,String(item.id)===selected)));
            if(marca.value) modelo.add(new Option('Outro / não listado','outro'));
            modelo.disabled=!marca.value||outra;
            if(outra){modelo.value='outro';modeloCustom.hidden=false;modeloCustom.required=true;}else toggleModelo();
        };
        const toggleModelo=()=>{const outro=modelo.value==='outro';modeloCustom.hidden=!outro;modeloCustom.required=outro;};
        marca.addEventListener('change',refresh);modelo.addEventListener('change',toggleModelo);refresh();
    }

    const vehicleSelect=document.getElementById('maintenance-vehicle');
    const vehiclePreview=document.getElementById('maintenance-vehicle-preview');
    if(vehicleSelect&&vehiclePreview){const refreshVehicle=()=>{const o=vehicleSelect.selectedOptions[0];vehiclePreview.hidden=!o?.value;if(!o?.value)return;const photo=vehiclePreview.querySelector('.preview-photo');photo.innerHTML=o.dataset.photo?`<img src="${o.dataset.photo}" alt="" style="object-position:${o.dataset.position}">`:'◇';vehiclePreview.querySelector('strong').textContent=o.dataset.name;vehiclePreview.querySelector('span').textContent=o.dataset.detail;const km=document.getElementById('maintenance-km');if(km&&!km.value)km.value=o.dataset.km||'';};vehicleSelect.addEventListener('change',refreshVehicle);refreshVehicle();}
    const systemSelect=document.getElementById('maintenance-system');
    const serviceSelect=document.getElementById('maintenance-service');
    if(systemSelect&&serviceSelect){
        const services=[...serviceSelect.querySelectorAll('option[data-system]')].map(option=>({value:option.value,label:option.textContent,system:option.dataset.system}));
        let selected=String(serviceSelect.dataset.selected||'');
        const refreshServices=()=>{
            const system=systemSelect.value;
            serviceSelect.innerHTML='';
            serviceSelect.add(new Option(system?'Selecione o serviço':'Selecione primeiro o sistema',''));
            services.filter(item=>item.system===system).forEach(item=>serviceSelect.add(new Option(item.label,item.value,false,item.value===selected)));
            serviceSelect.disabled=!system;
            if(!services.some(item=>item.value===selected&&item.system===system))selected='';
        };
        systemSelect.addEventListener('change',()=>{selected='';refreshServices();});
        serviceSelect.addEventListener('change',()=>{selected=serviceSelect.value;});
        refreshServices();
    }
    document.getElementById('check-all-ok')?.addEventListener('click',()=>document.querySelectorAll('.condition.ok input').forEach(input=>{input.checked=true;input.dispatchEvent(new Event('change',{bubbles:true}));}));
});
