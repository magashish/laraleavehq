<x-app-layout>
<style>
.tp { font-size:12px;padding:5px 14px;border-radius:99px;border:1px solid #e0e0e0;background:#f5f5f3;color:#666;cursor:pointer;white-space:nowrap;transition:all .15s; }
.tp:hover { background:#ebebeb; }
.tp.active { background:#fff;color:#1a1a1a;border-color:#aaa;font-weight:500; }
.ov-stat { background:#f5f5f3;border-radius:10px;padding:14px 16px; }
.pill { font-size:10px;padding:3px 9px;border-radius:99px;font-weight:500;white-space:nowrap; }
.p-in  { background:#ddeeff;color:#1558a0; }
.p-re  { background:#d8f5ec;color:#0d6648; }
.p-le  { background:#efefed;color:#555; }
.p-si  { background:#fde8e8;color:#a02020; }
.p-off { background:#f0f0ee;color:#999; }
.day-cell { width:32px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.dc-in   { background:#ddeeff; }  .dc-re  { background:#d8f5ec; }
.dc-le   { background:#efefed; }  .dc-si  { background:#fde8e8; }
.dc-off  { background:#f5f5f3; }
.dcl-in  { color:#1558a0; }       .dcl-re { color:#0d6648; }
.dcl-le  { color:#555; }          .dcl-si { color:#a02020; }
.dcl-off { color:#bbb; }
.today-col { outline:1.5px solid #3a8ddd;border-radius:6px; }
.nb-warn { background:#fff0d8;color:#7a4800; }
.nb-info { background:#ddeeff;color:#1558a0; }
.fbtn { font-size:11px;padding:3px 10px;border-radius:99px;border:1px solid #e0e0e0;background:#f5f5f3;color:#888;cursor:pointer; }
.fbtn.on { background:#fff;color:#1a1a1a;border-color:#aaa;font-weight:500; }
.ov-card { background:#fff;border:1px solid #ebebeb;border-radius:14px;padding:1rem 1.25rem; }
.prow { display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f0f0ee; }
.prow:last-child { border-bottom:none; }
</style>

<div class="page" x-data="teamOverview()">

    {{-- ── Header ── --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="font-size:20px;font-weight:500;color:#1a1a1a;">Team overview</h2>
            <div style="font-size:12px;color:#888;margin-top:2px;" x-text="periodSub"></div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <button class="tp" :class="view==='today'?'active':''" @click="setView('today')">Today</button>
                <button class="tp" :class="view==='week'?'active':''"  @click="setView('week')">This week</button>
                <button class="tp" :class="view==='month'?'active':''" @click="setView('month')">This month</button>
            </div>
            <span style="font-size:11px;color:#999;background:#f5f5f3;border:1px solid #e0e0e0;border-radius:8px;padding:3px 10px;" x-text="rangeLabel"></span>
        </div>
    </div>

    {{-- ── Stats row ── --}}
    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:1.25rem;">
        <template x-for="s in stats" :key="s.label">
            <div class="ov-stat">
                <div style="font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;" x-text="s.label"></div>
                <div style="font-size:28px;font-weight:500;" :style="'color:'+s.color" x-text="s.value"></div>
                <div style="font-size:11px;color:#888;margin-top:3px;" x-text="s.sub"></div>
            </div>
        </template>
    </div>

    {{-- ── Segment bar ── --}}
    <div class="ov-card" style="margin-bottom:12px;">
        <div style="font-size:12px;font-weight:500;color:#888;letter-spacing:.02em;margin-bottom:12px;" x-text="segTitle"></div>
        <div style="height:28px;width:100%;border-radius:10px;overflow:hidden;display:flex;margin-bottom:10px;">
            <template x-if="seg.office>0">
                <div :style="'width:'+pct(seg.office)+'%;background:#3a7dcc;display:flex;align-items:center;justify-content:center;'">
                    <span style="font-size:10px;font-weight:600;color:#fff;padding:0 5px;" x-text="seg.office"></span>
                </div>
            </template>
            <template x-if="seg.remote>0">
                <div :style="'width:'+pct(seg.remote)+'%;background:#1d9e75;display:flex;align-items:center;justify-content:center;'">
                    <span style="font-size:10px;font-weight:600;color:#fff;padding:0 5px;" x-text="seg.remote"></span>
                </div>
            </template>
            <template x-if="seg.leave>0">
                <div :style="'width:'+pct(seg.leave)+'%;background:#b4b2a9;display:flex;align-items:center;justify-content:center;'">
                    <span style="font-size:10px;font-weight:600;color:#fff;padding:0 5px;" x-text="seg.leave"></span>
                </div>
            </template>
            <template x-if="seg.sick>0">
                <div :style="'width:'+pct(seg.sick)+'%;background:#e24b4a;display:flex;align-items:center;justify-content:center;'">
                    <span style="font-size:10px;font-weight:600;color:#fff;padding:0 5px;" x-text="seg.sick"></span>
                </div>
            </template>
            <template x-if="seg.unknown>0">
                <div :style="'width:'+pct(seg.unknown)+'%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;'">
                    <span style="font-size:10px;font-weight:600;color:#aaa;padding:0 5px;" x-text="seg.unknown"></span>
                </div>
            </template>
        </div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;border-radius:50%;background:#3a7dcc;display:inline-block;"></span>In office</span>
            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;border-radius:50%;background:#1d9e75;display:inline-block;"></span>Remote</span>
            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;border-radius:50%;background:#b4b2a9;display:inline-block;"></span>On leave</span>
            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;border-radius:50%;background:#e24b4a;display:inline-block;"></span>Sick</span>
            <template x-if="view==='today'">
                <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;border-radius:50%;background:#e0e0e0;display:inline-block;"></span>Not checked in</span>
            </template>
        </div>
    </div>

    {{-- ── Today / Week: people + notices ── --}}
    <template x-if="view==='today' || view==='week'">
        <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;margin-bottom:12px;">

            {{-- People card --}}
            <div class="ov-card" style="overflow-x:auto;">
                <div style="font-size:12px;font-weight:500;color:#888;letter-spacing:.02em;margin-bottom:12px;"
                     x-text="view==='today' ? 'Team — today' : 'Team schedule — this week'"></div>

                {{-- Today filters --}}
                <template x-if="view==='today'">
                    <div style="display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap;">
                        <button class="fbtn" :class="filter==='all'?'on':''"    @click="filter='all'">All</button>
                        <button class="fbtn" :class="filter==='office'?'on':''" @click="filter='office'">In office</button>
                        <button class="fbtn" :class="filter==='remote'?'on':''" @click="filter='remote'">Remote</button>
                        <button class="fbtn" :class="filter==='leave'?'on':''"  @click="filter='leave'">On leave</button>
                        <button class="fbtn" :class="filter==='sick'?'on':''"   @click="filter='sick'">Sick</button>
                    </div>
                </template>

                {{-- Today: person rows --}}
                <template x-if="view==='today'">
                    <div>
                        <template x-for="p in filteredPeople" :key="p.id">
                            <div class="prow">
                                <div class="avatar" style="width:32px;height:32px;font-size:11px;font-weight:500;flex-shrink:0;"
                                     :style="'background:'+p.color+'33;color:'+p.color" x-text="p.initials"></div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:500;color:#1a1a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="p.name"></div>
                                    <div style="font-size:11px;color:#888;" x-text="p.role"></div>
                                </div>
                                <span :class="pillCls(p.status)" x-text="statusLabel(p.status)"></span>
                                <span style="font-size:11px;color:#bbb;min-width:44px;text-align:right;" x-text="p.time"></span>
                            </div>
                        </template>
                        <template x-if="filteredPeople.length===0">
                            <div style="font-size:13px;color:#aaa;padding:16px 0;text-align:center;">No team members match this filter.</div>
                        </template>
                    </div>
                </template>

                {{-- Week: day grid --}}
                <template x-if="view==='week'">
                    <div>
                        <div style="display:flex;align-items:center;gap:6px;padding:0 0 8px;border-bottom:1px solid #f0f0ee;margin-bottom:4px;">
                            <div style="width:32px;flex-shrink:0;"></div>
                            <div style="width:124px;flex-shrink:0;"></div>
                            <template x-for="(day,i) in weekLabels" :key="i">
                                <div style="font-size:10px;font-weight:500;text-align:center;width:32px;flex-shrink:0;"
                                     :style="i===todayIdx ? 'color:#3a8ddd' : 'color:#aaa'" x-text="day"></div>
                            </template>
                        </div>
                        <template x-for="p in teamData" :key="p.id">
                            <div style="display:flex;align-items:center;gap:6px;padding:8px 0;border-bottom:1px solid #f0f0ee;">
                                <div class="avatar" style="width:32px;height:32px;font-size:11px;font-weight:500;flex-shrink:0;"
                                     :style="'background:'+p.color+'33;color:'+p.color" x-text="p.initials"></div>
                                <div style="width:124px;flex-shrink:0;min-width:0;">
                                    <div style="font-size:12px;font-weight:500;color:#1a1a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="p.name"></div>
                                    <div style="font-size:11px;color:#888;" x-text="p.role"></div>
                                </div>
                                <template x-for="(s,di) in p.week" :key="di">
                                    <div class="day-cell" :class="[dayCellCls(s), di===todayIdx ? 'today-col' : '']">
                                        <span style="font-size:9px;font-weight:500;" :class="dayLblCls(s)" x-text="dayLbl(s)"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
                            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#ddeeff;border:1px solid #b5d4f4;display:inline-block;"></span>In</span>
                            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#d8f5ec;border:1px solid #9fe1cb;display:inline-block;"></span>WFH</span>
                            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#efefed;border:1px solid #d3d1c7;display:inline-block;"></span>Leave</span>
                            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#fde8e8;border:1px solid #f7c1c1;display:inline-block;"></span>Sick</span>
                            <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#f5f5f3;border:1px solid #e0e0e0;display:inline-block;"></span>—</span>
                            <template x-if="todayIdx !== null">
                                <span style="font-size:10px;color:#3a8ddd;font-weight:500;">| Today</span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Notices card --}}
            <div class="ov-card">
                <div style="font-size:12px;font-weight:500;color:#888;letter-spacing:.02em;margin-bottom:12px;">Notices</div>
                <template x-if="notices.length===0">
                    <div style="font-size:13px;color:#aaa;padding:8px 0;">Nothing to flag today.</div>
                </template>
                <template x-for="n in notices" :key="n.title">
                    <div style="display:flex;align-items:flex-start;gap:8px;padding:8px 0;border-bottom:1px solid #f0f0ee;">
                        <span :class="n.type==='warn' ? 'nb-warn' : 'nb-info'"
                              style="font-size:10px;padding:2px 7px;border-radius:99px;font-weight:500;flex-shrink:0;margin-top:1px;"
                              x-text="n.type==='warn' ? 'Alert' : 'Info'"></span>
                        <div>
                            <div style="font-size:12px;color:#1a1a1a;line-height:1.4;" x-text="n.title"></div>
                            <div style="font-size:10px;color:#aaa;margin-top:1px;" x-text="n.meta"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- ── Month view ── --}}
    <template x-if="view==='month'">
        <div>
            <div class="ov-card">
                <div style="font-size:12px;font-weight:500;color:#888;letter-spacing:.02em;margin-bottom:12px;">In office / remote days per person — this month</div>
                <template x-for="p in teamData" :key="p.id">
                    @php $workingDays = now()->daysInMonth; @endphp
                    <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f0f0ee;">
                        <div class="avatar" style="width:32px;height:32px;font-size:11px;font-weight:500;flex-shrink:0;"
                             :style="'background:'+p.color+'33;color:'+p.color" x-text="p.initials"></div>
                        <div style="width:124px;flex-shrink:0;min-width:0;">
                            <div style="font-size:12px;font-weight:500;color:#1a1a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="p.name"></div>
                            <div style="font-size:11px;color:#888;" x-text="p.role"></div>
                        </div>
                        <div style="flex:1;height:14px;background:#f0f0ee;border-radius:3px;overflow:hidden;display:flex;">
                            <div :style="'width:'+monthPct(p.month.office)+'%;background:#3a7dcc;height:100%;'"></div>
                            <div :style="'width:'+monthPct(p.month.remote)+'%;background:#1d9e75;height:100%;'"></div>
                            <div :style="'width:'+monthPct(p.month.leave)+'%;background:#b4b2a9;height:100%;'"></div>
                            <div :style="'width:'+monthPct(p.month.sick)+'%;background:#e24b4a;height:100%;'"></div>
                        </div>
                        <span style="font-size:10px;color:#bbb;width:64px;text-align:right;flex-shrink:0;"
                              x-text="p.month.office+'d in / '+p.month.remote+'d WFH'"></span>
                    </div>
                </template>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
                    <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#3a7dcc;display:inline-block;"></span>In office</span>
                    <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#1d9e75;display:inline-block;"></span>Remote</span>
                    <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#b4b2a9;display:inline-block;"></span>Leave</span>
                    <span style="font-size:11px;color:#888;display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#e24b4a;display:inline-block;"></span>Sick</span>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function teamOverview() {
    const teamData   = @json($teamData);
    const notices    = @json($notices);
    const weekLabels = @json($weekLabels);
    const todayIdx   = {{ $todayIdx ?? 'null' }};

    return {
        view: 'today',
        filter: 'all',
        teamData,
        notices,
        weekLabels,
        todayIdx,

        setView(v) { this.view = v; this.filter = 'all'; },

        get periodSub() {
            const d = new Date();
            if (this.view === 'today') return d.toLocaleDateString('en-GB', {weekday:'long',day:'numeric',month:'long',year:'numeric'});
            if (this.view === 'week')  return 'Week of ' + this.weekLabels[0] + ' – ' + this.weekLabels[4];
            return d.toLocaleDateString('en-GB', {month:'long',year:'numeric'});
        },

        get rangeLabel() {
            const d = new Date();
            if (this.view === 'today') return d.toLocaleDateString('en-GB', {weekday:'short',day:'numeric',month:'short',year:'numeric'});
            if (this.view === 'week')  return this.weekLabels[0] + ' – ' + this.weekLabels[4];
            return d.toLocaleDateString('en-GB', {month:'long',year:'numeric'});
        },

        get segTitle() {
            if (this.view === 'today') return 'Where is everyone today?';
            if (this.view === 'week')  return 'Location split — this week';
            return 'Location split — this month';
        },

        get seg() {
            const c = {office:0, remote:0, leave:0, sick:0, unknown:0};
            if (this.view === 'today') {
                this.teamData.forEach(p => { c[p.status] = (c[p.status] ?? 0) + 1; });
            } else if (this.view === 'week') {
                this.teamData.forEach(p => p.week.forEach(s => { c[s] = (c[s] ?? 0) + 1; }));
            } else {
                this.teamData.forEach(p => {
                    c.office += p.month.office || 0;
                    c.remote += p.month.remote || 0;
                    c.leave  += p.month.leave  || 0;
                    c.sick   += p.month.sick   || 0;
                });
            }
            return c;
        },

        pct(n) {
            const total = Object.values(this.seg).reduce((a,b) => a+b, 0) || 1;
            return Math.round(n / total * 100);
        },

        monthPct(n) {
            const total = 23; // ~working days in a month
            return Math.min(100, Math.round(n / total * 100));
        },

        get stats() {
            const total = this.teamData.length;
            const c = this.seg;
            if (this.view === 'today') return [
                {label:'Team size', value:total,           sub:'total employees',                      color:'#1a1a1a'},
                {label:'In office', value:c.office,        sub:Math.round(c.office/total*100)+'% of team', color:'#3a7dcc'},
                {label:'Remote',    value:c.remote,        sub:Math.round(c.remote/total*100)+'% of team', color:'#1d9e75'},
                {label:'Off today', value:c.leave+c.sick,  sub:c.leave+' leave · '+c.sick+' sick',    color:'#c03030'},
            ];
            if (this.view === 'week') return [
                {label:'Team size',       value:total,          sub:'total employees',          color:'#1a1a1a'},
                {label:'In office days',  value:c.office,       sub:'across the team',          color:'#3a7dcc'},
                {label:'Remote days',     value:c.remote,       sub:'across the team',          color:'#1d9e75'},
                {label:'Days off',        value:c.leave+c.sick, sub:c.leave+' leave · '+c.sick+' sick', color:'#c03030'},
            ];
            return [
                {label:'Team size',   value:total,          sub:'total employees',        color:'#1a1a1a'},
                {label:'Office days', value:c.office,       sub:'total this month',       color:'#3a7dcc'},
                {label:'Remote days', value:c.remote,       sub:'total this month',       color:'#1d9e75'},
                {label:'Days off',    value:c.leave+c.sick, sub:c.leave+' leave · '+c.sick+' sick', color:'#c03030'},
            ];
        },

        get filteredPeople() {
            if (this.filter === 'all') return this.teamData;
            return this.teamData.filter(p => p.status === this.filter);
        },

        statusLabel(s) {
            return {office:'In office',remote:'Remote',leave:'On leave',sick:'Sick',unknown:'Not checked in'}[s] || s;
        },
        pillCls(s)    { return ({office:'pill p-in',remote:'pill p-re',leave:'pill p-le',sick:'pill p-si',unknown:'pill p-off'}[s]||'pill p-off'); },
        dayCellCls(s) { return ({office:'dc-in',remote:'dc-re',leave:'dc-le',sick:'dc-si',unknown:'dc-off'}[s]||'dc-off'); },
        dayLblCls(s)  { return ({office:'dcl-in',remote:'dcl-re',leave:'dcl-le',sick:'dcl-si',unknown:'dcl-off'}[s]||'dcl-off'); },
        dayLbl(s)     { return ({office:'In',remote:'WFH',leave:'Lv',sick:'Sick',unknown:'—'}[s]||'—'); },
    };
}
</script>
</x-app-layout>
