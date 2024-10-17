// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

function Rt() {
  if (typeof globalThis < 'u') return globalThis
  if (typeof self < 'u') return self
  if (typeof window < 'u') return window
  if (typeof global < 'u') return global
}
function Lt() {
  const e = Rt()
  if (e.__xstate__) return e.__xstate__
}
const Jt = e => {
  if (typeof window > 'u') return
  const t = Lt()
  t && t.register(e)
}
class rt {
  constructor(t) {
    ;(this._process = t),
      (this._active = !1),
      (this._current = null),
      (this._last = null)
  }
  start() {
    ;(this._active = !0), this.flush()
  }
  clear() {
    this._current && ((this._current.next = null), (this._last = this._current))
  }
  enqueue(t) {
    const n = {value: t, next: null}
    if (this._current) {
      ;(this._last.next = n), (this._last = n)
      return
    }
    ;(this._current = n), (this._last = n), this._active && this.flush()
  }
  flush() {
    while (this._current) {
      const t = this._current
      this._process(t.value), (this._current = t.next)
    }
    this._last = null
  }
}
const yt = '.',
  Gt = '',
  gt = '',
  zt = '#',
  Bt = '*',
  vt = 'xstate.init',
  W = 'xstate.stop'
function Ut(e, t) {
  return {type: `xstate.after.${e}.${t}`}
}
function q(e, t) {
  return {type: `xstate.done.state.${e}`, output: t}
}
function Wt(e, t) {
  return {type: `xstate.done.actor.${e}`, output: t, actorId: e}
}
function qt(e, t) {
  return {type: `xstate.error.actor.${e}`, error: t, actorId: e}
}
function mt(e) {
  return {type: vt, input: e}
}
function _(e) {
  setTimeout(() => {
    throw e
  })
}
const Ht = (typeof Symbol == 'function' && Symbol.observable) || '@@observable'
function _t(e, t) {
  const n = ot(e),
    s = ot(t)
  return typeof s == 'string'
    ? typeof n == 'string'
      ? s === n
      : !1
    : typeof n == 'string'
      ? n in s
      : Object.keys(n).every(i => (i in s ? _t(n[i], s[i]) : !1))
}
function Z(e) {
  if (bt(e)) return e
  let t = [],
    n = ''
  for (let s = 0; s < e.length; s++) {
    switch (e.charCodeAt(s)) {
      case 92:
        ;(n += e[s + 1]), s++
        continue
      case 46:
        t.push(n), (n = '')
        continue
    }
    n += e[s]
  }
  return t.push(n), t
}
function ot(e) {
  if (xe(e)) return e.value
  if (typeof e != 'string') return e
  const t = Z(e)
  return Yt(t)
}
function Yt(e) {
  if (e.length === 1) return e[0]
  const t = {}
  let n = t
  for (let s = 0; s < e.length - 1; s++)
    if (s === e.length - 2) n[e[s]] = e[s + 1]
    else {
      const i = n
      ;(n = {}), (i[e[s]] = n)
    }
  return t
}
function ct(e, t) {
  const n = {},
    s = Object.keys(e)
  for (let i = 0; i < s.length; i++) {
    const r = s[i]
    n[r] = t(e[r], r, e, i)
  }
  return n
}
function St(e) {
  return bt(e) ? e : [e]
}
function S(e) {
  return e === void 0 ? [] : St(e)
}
function H(e, t, n, s) {
  return typeof e == 'function' ? e({context: t, event: n, self: s}) : e
}
function bt(e) {
  return Array.isArray(e)
}
function Xt(e) {
  return e.type.startsWith('xstate.error.actor')
}
function I(e) {
  return St(e).map(t =>
    typeof t > 'u' || typeof t == 'string' ? {target: t} : t,
  )
}
function wt(e) {
  if (!(e === void 0 || e === Gt)) return S(e)
}
function Y(e, t, n) {
  var r, o, c
  const s = typeof e == 'object',
    i = s ? e : void 0
  return {
    next: (r = s ? e.next : e) == null ? void 0 : r.bind(i),
    error: (o = s ? e.error : t) == null ? void 0 : o.bind(i),
    complete: (c = s ? e.complete : n) == null ? void 0 : c.bind(i),
  }
}
function at(e, t) {
  return `${t}.${e}`
}
function F(e, t) {
  const n = t.match(/^xstate\.invoke\.(\d+)\.(.*)/)
  if (!n) return e.implementations.actors[t]
  const [, s, i] = n,
    o = e.getStateNodeById(i).config.invoke
  return (Array.isArray(o) ? o[s] : o).src
}
function ht(e, t) {
  return `${e.sessionId}.${t}`
}
let Kt = 0
function Pt(e, t) {
  const n = new Map(),
    s = new Map(),
    i = new WeakMap(),
    r = new Set(),
    o = {},
    {clock: c, logger: u} = t,
    a = {
      schedule: (f, l, p, y, v = Math.random().toString(36).slice(2)) => {
        const w = {
            source: f,
            target: l,
            event: p,
            delay: y,
            id: v,
            startedAt: Date.now(),
          },
          m = ht(f, v)
        h._snapshot._scheduledEvents[m] = w
        const Dt = c.setTimeout(() => {
          delete o[m], delete h._snapshot._scheduledEvents[m], h._relay(f, l, p)
        }, y)
        o[m] = Dt
      },
      cancel: (f, l) => {
        const p = ht(f, l),
          y = o[p]
        delete o[p],
          delete h._snapshot._scheduledEvents[p],
          y !== void 0 && c.clearTimeout(y)
      },
      cancelAll: f => {
        for (const l in h._snapshot._scheduledEvents) {
          const p = h._snapshot._scheduledEvents[l]
          p.source === f && a.cancel(f, p.id)
        }
      },
    },
    d = f => {
      if (!r.size) return
      const l = {...f, rootId: e.sessionId}
      r.forEach(p => {
        var y
        return (y = p.next) == null ? void 0 : y.call(p, l)
      })
    },
    h = {
      _snapshot: {
        _scheduledEvents:
          ((t == null ? void 0 : t.snapshot) && t.snapshot.scheduler) ?? {},
      },
      _bookId: () => `x:${Kt++}`,
      _register: (f, l) => (n.set(f, l), f),
      _unregister: f => {
        n.delete(f.sessionId)
        const l = i.get(f)
        l !== void 0 && (s.delete(l), i.delete(f))
      },
      get: f => s.get(f),
      _set: (f, l) => {
        const p = s.get(f)
        if (p && p !== l)
          throw new Error(`Actor with system ID '${f}' already exists.`)
        s.set(f, l), i.set(l, f)
      },
      inspect: f => {
        const l = Y(f)
        return (
          r.add(l),
          {
            unsubscribe() {
              r.delete(l)
            },
          }
        )
      },
      _sendInspectionEvent: d,
      _relay: (f, l, p) => {
        h._sendInspectionEvent({
          type: '@xstate.event',
          sourceRef: f,
          actorRef: l,
          event: p,
        }),
          l._send(p)
      },
      scheduler: a,
      getSnapshot: () => ({
        _scheduledEvents: {...h._snapshot._scheduledEvents},
      }),
      start: () => {
        const f = h._snapshot._scheduledEvents
        h._snapshot._scheduledEvents = {}
        for (const l in f) {
          const {source: p, target: y, event: v, delay: w, id: m} = f[l]
          a.schedule(p, y, v, w, m)
        }
      },
      _clock: c,
      _logger: u,
    }
  return h
}
const Q = 1
const g = (e => (
  (e[(e.NotStarted = 0)] = 'NotStarted'),
  (e[(e.Running = 1)] = 'Running'),
  (e[(e.Stopped = 2)] = 'Stopped'),
  e
))({})
const Zt = {
  clock: {
    setTimeout: (e, t) => setTimeout(e, t),
    clearTimeout: e => clearTimeout(e),
  },
  logger: console.log.bind(console),
  devTools: !1,
}
class Ft {
  constructor(t, n) {
    ;(this.logic = t),
      (this._snapshot = void 0),
      (this.clock = void 0),
      (this.options = void 0),
      (this.id = void 0),
      (this.mailbox = new rt(this._process.bind(this))),
      (this.observers = new Set()),
      (this.eventListeners = new Map()),
      (this.logger = void 0),
      (this._processingStatus = g.NotStarted),
      (this._parent = void 0),
      (this._syncSnapshot = void 0),
      (this.ref = void 0),
      (this._actorScope = void 0),
      (this._systemId = void 0),
      (this.sessionId = void 0),
      (this.system = void 0),
      (this._doneEvent = void 0),
      (this.src = void 0),
      (this._deferred = [])
    const s = {...Zt, ...n},
      {
        clock: i,
        logger: r,
        parent: o,
        syncSnapshot: c,
        id: u,
        systemId: a,
        inspect: d,
      } = s
    ;(this.system = o ? o.system : Pt(this, {clock: i, logger: r})),
      d && !o && this.system.inspect(Y(d)),
      (this.sessionId = this.system._bookId()),
      (this.id = u ?? this.sessionId),
      (this.logger = (n == null ? void 0 : n.logger) ?? this.system._logger),
      (this.clock = (n == null ? void 0 : n.clock) ?? this.system._clock),
      (this._parent = o),
      (this._syncSnapshot = c),
      (this.options = s),
      (this.src = s.src ?? t),
      (this.ref = this),
      (this._actorScope = {
        self: this,
        id: this.id,
        sessionId: this.sessionId,
        logger: this.logger,
        defer: h => {
          this._deferred.push(h)
        },
        system: this.system,
        stopChild: h => {
          if (h._parent !== this)
            throw new Error(
              `Cannot stop child actor ${h.id} of ${this.id} because it is not a child`,
            )
          h._stop()
        },
        emit: h => {
          const f = this.eventListeners.get(h.type),
            l = this.eventListeners.get('*')
          if (!f && !l) return
          const p = new Set([
            ...(f ? f.values() : []),
            ...(l ? l.values() : []),
          ])
          for (const y of Array.from(p)) y(h)
        },
      }),
      (this.send = this.send.bind(this)),
      this.system._sendInspectionEvent({type: '@xstate.actor', actorRef: this}),
      a && ((this._systemId = a), this.system._set(a, this)),
      this._initState(
        (n == null ? void 0 : n.snapshot) ?? (n == null ? void 0 : n.state),
      ),
      a && this._snapshot.status !== 'active' && this.system._unregister(this)
  }
  _initState(t) {
    var n
    try {
      this._snapshot = t
        ? this.logic.restoreSnapshot
          ? this.logic.restoreSnapshot(t, this._actorScope)
          : t
        : this.logic.getInitialSnapshot(
            this._actorScope,
            (n = this.options) == null ? void 0 : n.input,
          )
    } catch (s) {
      this._snapshot = {status: 'error', output: void 0, error: s}
    }
  }
  update(t, n) {
    var i, r
    this._snapshot = t
    let s
    while ((s = this._deferred.shift()))
      try {
        s()
      } catch (o) {
        ;(this._deferred.length = 0),
          (this._snapshot = {...t, status: 'error', error: o})
      }
    switch (this._snapshot.status) {
      case 'active':
        for (const o of this.observers)
          try {
            ;(i = o.next) == null || i.call(o, t)
          } catch (c) {
            _(c)
          }
        break
      case 'done':
        for (const o of this.observers)
          try {
            ;(r = o.next) == null || r.call(o, t)
          } catch (c) {
            _(c)
          }
        this._stopProcedure(),
          this._complete(),
          (this._doneEvent = Wt(this.id, this._snapshot.output)),
          this._parent &&
            this.system._relay(this, this._parent, this._doneEvent)
        break
      case 'error':
        this._error(this._snapshot.error)
        break
    }
    this.system._sendInspectionEvent({
      type: '@xstate.snapshot',
      actorRef: this,
      event: n,
      snapshot: t,
    })
  }
  subscribe(t, n, s) {
    var r
    const i = Y(t, n, s)
    if (this._processingStatus !== g.Stopped) this.observers.add(i)
    else
      switch (this._snapshot.status) {
        case 'done':
          try {
            ;(r = i.complete) == null || r.call(i)
          } catch (o) {
            _(o)
          }
          break
        case 'error': {
          const o = this._snapshot.error
          if (!i.error) _(o)
          else
            try {
              i.error(o)
            } catch (c) {
              _(c)
            }
          break
        }
      }
    return {
      unsubscribe: () => {
        this.observers.delete(i)
      },
    }
  }
  on(t, n) {
    let s = this.eventListeners.get(t)
    s || ((s = new Set()), this.eventListeners.set(t, s))
    const i = n.bind(void 0)
    return (
      s.add(i),
      {
        unsubscribe: () => {
          s.delete(i)
        },
      }
    )
  }
  start() {
    if (this._processingStatus === g.Running) return this
    this._syncSnapshot &&
      this.subscribe({
        next: s => {
          s.status === 'active' &&
            this.system._relay(this, this._parent, {
              type: `xstate.snapshot.${this.id}`,
              snapshot: s,
            })
        },
        error: () => {},
      }),
      this.system._register(this.sessionId, this),
      this._systemId && this.system._set(this._systemId, this),
      (this._processingStatus = g.Running)
    const t = mt(this.options.input)
    switch (
      (this.system._sendInspectionEvent({
        type: '@xstate.event',
        sourceRef: this._parent,
        actorRef: this,
        event: t,
      }),
      this._snapshot.status)
    ) {
      case 'done':
        return this.update(this._snapshot, t), this
      case 'error':
        return this._error(this._snapshot.error), this
    }
    if ((this._parent || this.system.start(), this.logic.start))
      try {
        this.logic.start(this._snapshot, this._actorScope)
      } catch (s) {
        return (
          (this._snapshot = {...this._snapshot, status: 'error', error: s}),
          this._error(s),
          this
        )
      }
    return (
      this.update(this._snapshot, t),
      this.options.devTools && this.attachDevTools(),
      this.mailbox.start(),
      this
    )
  }
  _process(t) {
    let n, s
    try {
      n = this.logic.transition(this._snapshot, t, this._actorScope)
    } catch (i) {
      s = {err: i}
    }
    if (s) {
      const {err: i} = s
      ;(this._snapshot = {...this._snapshot, status: 'error', error: i}),
        this._error(i)
      return
    }
    this.update(n, t), t.type === W && (this._stopProcedure(), this._complete())
  }
  _stop() {
    return this._processingStatus === g.Stopped
      ? this
      : (this.mailbox.clear(),
        this._processingStatus === g.NotStarted
          ? ((this._processingStatus = g.Stopped), this)
          : (this.mailbox.enqueue({type: W}), this))
  }
  stop() {
    if (this._parent)
      throw new Error('A non-root actor cannot be stopped directly.')
    return this._stop()
  }
  _complete() {
    var t
    for (const n of this.observers)
      try {
        ;(t = n.complete) == null || t.call(n)
      } catch (s) {
        _(s)
      }
    this.observers.clear()
  }
  _reportError(t) {
    if (!this.observers.size) {
      this._parent || _(t)
      return
    }
    let n = !1
    for (const s of this.observers) {
      const i = s.error
      n || (n = !i)
      try {
        i == null || i(t)
      } catch (r) {
        _(r)
      }
    }
    this.observers.clear(), n && _(t)
  }
  _error(t) {
    this._stopProcedure(),
      this._reportError(t),
      this._parent && this.system._relay(this, this._parent, qt(this.id, t))
  }
  _stopProcedure() {
    return this._processingStatus !== g.Running
      ? this
      : (this.system.scheduler.cancelAll(this),
        this.mailbox.clear(),
        (this.mailbox = new rt(this._process.bind(this))),
        (this._processingStatus = g.Stopped),
        this.system._unregister(this),
        this)
  }
  _send(t) {
    this._processingStatus !== g.Stopped && this.mailbox.enqueue(t)
  }
  send(t) {
    this.system._relay(void 0, this, t)
  }
  attachDevTools() {
    const {devTools: t} = this.options
    t && (typeof t == 'function' ? t : Jt)(this)
  }
  toJSON() {
    return {xstate$$type: Q, id: this.id}
  }
  getPersistedSnapshot(t) {
    return this.logic.getPersistedSnapshot(this._snapshot, t)
  }
  [Ht]() {
    return this
  }
  getSnapshot() {
    return this._snapshot
  }
}
function C(e, ...[t]) {
  return new Ft(e, t)
}
function Qt(e, t, n, s, {sendId: i}) {
  const r = typeof i == 'function' ? i(n, s) : i
  return [t, r]
}
function Vt(e, t) {
  e.defer(() => {
    e.system.scheduler.cancel(e.self, t)
  })
}
function Nt(e) {
  function t(n, s) {}
  return (
    (t.type = 'xstate.cancel'),
    (t.sendId = e),
    (t.resolve = Qt),
    (t.execute = Vt),
    t
  )
}
function te(
  e,
  t,
  n,
  s,
  {id: i, systemId: r, src: o, input: c, syncSnapshot: u},
) {
  const a = typeof o == 'string' ? F(t.machine, o) : o,
    d = typeof i == 'function' ? i(n) : i
  let h
  return (
    a &&
      (h = C(a, {
        id: d,
        src: o,
        parent: e.self,
        syncSnapshot: u,
        systemId: r,
        input:
          typeof c == 'function'
            ? c({context: t.context, event: n.event, self: e.self})
            : c,
      })),
    [E(t, {children: {...t.children, [d]: h}}), {id: i, actorRef: h}]
  )
}
function ee(e, {id: t, actorRef: n}) {
  n &&
    e.defer(() => {
      n._processingStatus !== g.Stopped && n.start()
    })
}
function ne(...[e, {id: t, systemId: n, input: s, syncSnapshot: i = !1} = {}]) {
  function r(o, c) {}
  return (
    (r.type = 'snapshot.spawnChild'),
    (r.id = t),
    (r.systemId = n),
    (r.src = e),
    (r.input = s),
    (r.syncSnapshot = i),
    (r.resolve = te),
    (r.execute = ee),
    r
  )
}
function se(e, t, n, s, {actorRef: i}) {
  const r = typeof i == 'function' ? i(n, s) : i,
    o = typeof r == 'string' ? t.children[r] : r
  let c = t.children
  return o && ((c = {...c}), delete c[o.id]), [E(t, {children: c}), o]
}
function ie(e, t) {
  if (t) {
    if ((e.system._unregister(t), t._processingStatus !== g.Running)) {
      e.stopChild(t)
      return
    }
    e.defer(() => {
      e.stopChild(t)
    })
  }
}
function xt(e) {
  function t(n, s) {}
  return (
    (t.type = 'xstate.stopChild'),
    (t.actorRef = e),
    (t.resolve = se),
    (t.execute = ie),
    t
  )
}
function V(e, t, n, s) {
  const {machine: i} = s,
    r = typeof e == 'function',
    o = r ? e : i.implementations.guards[typeof e == 'string' ? e : e.type]
  if (!r && !o)
    throw new Error(
      `Guard '${typeof e == 'string' ? e : e.type}' is not implemented.'.`,
    )
  if (typeof o != 'function') return V(o, t, n, s)
  const c = {context: t, event: n},
    u =
      r || typeof e == 'string'
        ? void 0
        : 'params' in e
          ? typeof e.params == 'function'
            ? e.params({context: t, event: n})
            : e.params
          : void 0
  return 'check' in o ? o.check(s, c, o) : o(c, u)
}
const N = e => e.type === 'atomic' || e.type === 'final'
function $(e) {
  return Object.values(e.states).filter(t => t.type !== 'history')
}
function D(e, t) {
  const n = []
  if (t === e) return n
  let s = e.parent
  while (s && s !== t) n.push(s), (s = s.parent)
  return n
}
function L(e) {
  const t = new Set(e),
    n = Et(t)
  for (const s of t)
    if (s.type === 'compound' && (!n.get(s) || !n.get(s).length))
      ut(s).forEach(i => t.add(i))
    else if (s.type === 'parallel') {
      for (const i of $(s))
        if (i.type !== 'history' && !t.has(i)) {
          const r = ut(i)
          for (const o of r) t.add(o)
        }
    }
  for (const s of t) {
    let i = s.parent
    while (i) t.add(i), (i = i.parent)
  }
  return t
}
function Tt(e, t) {
  const n = t.get(e)
  if (!n) return {}
  if (e.type === 'compound') {
    const i = n[0]
    if (i) {
      if (N(i)) return i.key
    } else return {}
  }
  const s = {}
  for (const i of n) s[i.key] = Tt(i, t)
  return s
}
function Et(e) {
  const t = new Map()
  for (const n of e)
    t.has(n) || t.set(n, []),
      n.parent &&
        (t.has(n.parent) || t.set(n.parent, []), t.get(n.parent).push(n))
  return t
}
function kt(e, t) {
  const n = L(t)
  return Tt(e, Et(n))
}
function tt(e, t) {
  return t.type === 'compound'
    ? $(t).some(n => n.type === 'final' && e.has(n))
    : t.type === 'parallel'
      ? $(t).every(n => tt(e, n))
      : t.type === 'final'
}
const z = e => e[0] === zt
function re(e, t) {
  return (
    e.transitions.get(t) ||
    [...e.transitions.keys()]
      .filter(s => {
        if (s === Bt) return !0
        if (!s.endsWith('.*')) return !1
        const i = s.split('.'),
          r = t.split('.')
        for (let o = 0; o < i.length; o++) {
          const c = i[o],
            u = r[o]
          if (c === '*') return o === i.length - 1
          if (c !== u) return !1
        }
        return !0
      })
      .sort((s, i) => i.length - s.length)
      .flatMap(s => e.transitions.get(s))
  )
}
function oe(e) {
  const t = e.config.after
  if (!t) return []
  const n = (i, r) => {
    const o = Ut(i, e.id),
      c = o.type
    return e.entry.push(je(o, {id: c, delay: i})), e.exit.push(Nt(c)), c
  }
  return Object.keys(t)
    .flatMap((i, r) => {
      const o = t[i],
        c = typeof o == 'string' ? {target: o} : o,
        u = Number.isNaN(+i) ? i : +i,
        a = n(u)
      return S(c).map(d => ({...d, event: a, delay: u}))
    })
    .map(i => {
      const {delay: r} = i
      return {...x(e, i.event, i), delay: r}
    })
}
function x(e, t, n) {
  const s = wt(n.target),
    i = n.reenter ?? !1,
    r = he(e, s),
    o = {
      ...n,
      actions: S(n.actions),
      guard: n.guard,
      target: r,
      source: e,
      reenter: i,
      eventType: t,
      toJSON: () => ({
        ...o,
        source: `#${e.id}`,
        target: r ? r.map(c => `#${c.id}`) : void 0,
      }),
    }
  return o
}
function ce(e) {
  const t = new Map()
  if (e.config.on)
    for (const n of Object.keys(e.config.on)) {
      if (n === gt)
        throw new Error(
          'Null events ("") cannot be specified as a transition key. Use `always: { ... }` instead.',
        )
      const s = e.config.on[n]
      t.set(
        n,
        I(s).map(i => x(e, n, i)),
      )
    }
  if (e.config.onDone) {
    const n = `xstate.done.state.${e.id}`
    t.set(
      n,
      I(e.config.onDone).map(s => x(e, n, s)),
    )
  }
  for (const n of e.invoke) {
    if (n.onDone) {
      const s = `xstate.done.actor.${n.id}`
      t.set(
        s,
        I(n.onDone).map(i => x(e, s, i)),
      )
    }
    if (n.onError) {
      const s = `xstate.error.actor.${n.id}`
      t.set(
        s,
        I(n.onError).map(i => x(e, s, i)),
      )
    }
    if (n.onSnapshot) {
      const s = `xstate.snapshot.${n.id}`
      t.set(
        s,
        I(n.onSnapshot).map(i => x(e, s, i)),
      )
    }
  }
  for (const n of e.after) {
    let s = t.get(n.eventType)
    s || ((s = []), t.set(n.eventType, s)), s.push(n)
  }
  return t
}
function ae(e, t) {
  const n = typeof t == 'string' ? e.states[t] : t ? e.states[t.target] : void 0
  if (!n && t)
    throw new Error(
      `Initial state node "${t}" not found on parent state node #${e.id}`,
    )
  const s = {
    source: e,
    actions: !t || typeof t == 'string' ? [] : S(t.actions),
    eventType: null,
    reenter: !1,
    target: n ? [n] : [],
    toJSON: () => ({...s, source: `#${e.id}`, target: n ? [`#${n.id}`] : []}),
  }
  return s
}
function he(e, t) {
  if (t !== void 0)
    return t.map(n => {
      if (typeof n != 'string') return n
      if (z(n)) return e.machine.getStateNodeById(n)
      const s = n[0] === yt
      if (s && !e.parent) return J(e, n.slice(1))
      const i = s ? e.key + n : n
      if (e.parent)
        try {
          return J(e.parent, i)
        } catch (r) {
          throw new Error(`Invalid transition definition for state node '${e.id}':
${r.message}`)
        }
      else
        throw new Error(
          `Invalid target: "${n}" is not a valid target from the root node. Did you mean ".${n}"?`,
        )
    })
}
function It(e) {
  const t = wt(e.config.target)
  return t
    ? {target: t.map(n => (typeof n == 'string' ? J(e.parent, n) : n))}
    : e.parent.initial
}
function T(e) {
  return e.type === 'history'
}
function ut(e) {
  const t = At(e)
  for (const n of t) for (const s of D(n, e)) t.add(s)
  return t
}
function At(e) {
  const t = new Set()
  function n(s) {
    if (!t.has(s)) {
      if ((t.add(s), s.type === 'compound')) n(s.initial.target[0])
      else if (s.type === 'parallel') for (const i of $(s)) n(i)
    }
  }
  return n(e), t
}
function O(e, t) {
  if (z(t)) return e.machine.getStateNodeById(t)
  if (!e.states)
    throw new Error(
      `Unable to retrieve child state '${t}' from '${e.id}'; no child states exist.`,
    )
  const n = e.states[t]
  if (!n) throw new Error(`Child state '${t}' does not exist on '${e.id}'`)
  return n
}
function J(e, t) {
  if (typeof t == 'string' && z(t))
    try {
      return e.machine.getStateNodeById(t)
    } catch {}
  const n = Z(t).slice()
  let s = e
  while (n.length) {
    const i = n.shift()
    if (!i.length) break
    s = O(s, i)
  }
  return s
}
function G(e, t) {
  if (typeof t == 'string') {
    const i = e.states[t]
    if (!i) throw new Error(`State '${t}' does not exist on '${e.id}'`)
    return [e, i]
  }
  const n = Object.keys(t),
    s = n.map(i => O(e, i)).filter(Boolean)
  return [e.machine.root, e].concat(
    s,
    n.reduce((i, r) => {
      const o = O(e, r)
      if (!o) return i
      const c = G(o, t[r])
      return i.concat(c)
    }, []),
  )
}
function ue(e, t, n, s) {
  const r = O(e, t).next(n, s)
  return !r || !r.length ? e.next(n, s) : r
}
function fe(e, t, n, s) {
  const i = Object.keys(t),
    r = O(e, i[0]),
    o = et(r, t[i[0]], n, s)
  return !o || !o.length ? e.next(n, s) : o
}
function de(e, t, n, s) {
  const i = []
  for (const r of Object.keys(t)) {
    const o = t[r]
    if (!o) continue
    const c = O(e, r),
      u = et(c, o, n, s)
    u && i.push(...u)
  }
  return i.length ? i : e.next(n, s)
}
function et(e, t, n, s) {
  return typeof t == 'string'
    ? ue(e, t, n, s)
    : Object.keys(t).length === 1
      ? fe(e, t, n, s)
      : de(e, t, n, s)
}
function le(e) {
  return Object.keys(e.states)
    .map(t => e.states[t])
    .filter(t => t.type === 'history')
}
function b(e, t) {
  let n = e
  while (n.parent && n.parent !== t) n = n.parent
  return n.parent === t
}
function pe(e, t) {
  const n = new Set(e),
    s = new Set(t)
  for (const i of n) if (s.has(i)) return !0
  for (const i of s) if (n.has(i)) return !0
  return !1
}
function $t(e, t, n) {
  const s = new Set()
  for (const i of e) {
    let r = !1
    const o = new Set()
    for (const c of s)
      if (pe(X([i], t, n), X([c], t, n)))
        if (b(i.source, c.source)) o.add(c)
        else {
          r = !0
          break
        }
    if (!r) {
      for (const c of o) s.delete(c)
      s.add(i)
    }
  }
  return Array.from(s)
}
function ye(e) {
  const [t, ...n] = e
  for (const s of D(t, void 0)) if (n.every(i => b(i, s))) return s
}
function nt(e, t) {
  if (!e.target) return []
  const n = new Set()
  for (const s of e.target)
    if (T(s))
      if (t[s.id]) for (const i of t[s.id]) n.add(i)
      else for (const i of nt(It(s), t)) n.add(i)
    else n.add(s)
  return [...n]
}
function Ot(e, t) {
  const n = nt(e, t)
  if (!n) return
  if (!e.reenter && n.every(i => i === e.source || b(i, e.source)))
    return e.source
  const s = ye(n.concat(e.source))
  if (s) return s
  if (!e.reenter) return e.source.machine.root
}
function X(e, t, n) {
  var i
  const s = new Set()
  for (const r of e)
    if ((i = r.target) != null && i.length) {
      const o = Ot(r, n)
      r.reenter && r.source === o && s.add(o)
      for (const c of t) b(c, o) && s.add(c)
    }
  return [...s]
}
function ge(e, t) {
  if (e.length !== t.size) return !1
  for (const n of e) if (!t.has(n)) return !1
  return !0
}
function K(e, t, n, s, i, r) {
  if (!e.length) return t
  const o = new Set(t._nodes)
  let c = t.historyValue
  const u = $t(e, o, c)
  let a = t
  i || ([a, c] = Se(a, s, n, u, o, c, r)),
    (a = M(
      a,
      s,
      n,
      u.flatMap(h => h.actions),
      r,
    )),
    (a = me(a, s, n, u, o, r, c, i))
  const d = [...o]
  a.status === 'done' &&
    (a = M(
      a,
      s,
      n,
      d.sort((h, f) => f.order - h.order).flatMap(h => h.exit),
      r,
    ))
  try {
    return c === t.historyValue && ge(t._nodes, o)
      ? a
      : E(a, {_nodes: d, historyValue: c})
  } catch (h) {
    throw h
  }
}
function ve(e, t, n, s, i) {
  if (s.output === void 0) return
  const r = q(
    i.id,
    i.output !== void 0 && i.parent
      ? H(i.output, e.context, t, n.self)
      : void 0,
  )
  return H(s.output, e.context, r, n.self)
}
function me(e, t, n, s, i, r, o, c) {
  let u = e
  const a = new Set(),
    d = new Set()
  _e(s, o, d, a), c && d.add(e.machine.root)
  const h = new Set()
  for (const f of [...a].sort((l, p) => l.order - p.order)) {
    i.add(f)
    const l = []
    l.push(...f.entry)
    for (const p of f.invoke)
      l.push(ne(p.src, {...p, syncSnapshot: !!p.onSnapshot}))
    if (d.has(f)) {
      const p = f.initial.actions
      l.push(...p)
    }
    if (
      ((u = M(
        u,
        t,
        n,
        l,
        r,
        f.invoke.map(p => p.id),
      )),
      f.type === 'final')
    ) {
      const p = f.parent
      let y =
          (p == null ? void 0 : p.type) === 'parallel'
            ? p
            : p == null
              ? void 0
              : p.parent,
        v = y || f
      for (
        (p == null ? void 0 : p.type) === 'compound' &&
        r.push(
          q(
            p.id,
            f.output !== void 0 ? H(f.output, u.context, t, n.self) : void 0,
          ),
        );
        (y == null ? void 0 : y.type) === 'parallel' && !h.has(y) && tt(i, y);
      )
        h.add(y), r.push(q(y.id)), (v = y), (y = y.parent)
      if (y) continue
      u = E(u, {status: 'done', output: ve(u, t, n, u.machine.root, v)})
    }
  }
  return u
}
function _e(e, t, n, s) {
  for (const i of e) {
    const r = Ot(i, t)
    for (const c of i.target || [])
      !T(c) &&
        (i.source !== c || i.source !== r || i.reenter) &&
        (s.add(c), n.add(c)),
        A(c, t, n, s)
    const o = nt(i, t)
    for (const c of o) {
      const u = D(c, r)
      ;(r == null ? void 0 : r.type) === 'parallel' && u.push(r),
        Mt(s, t, n, u, !i.source.parent && i.reenter ? void 0 : r)
    }
  }
}
function A(e, t, n, s) {
  var i
  if (T(e))
    if (t[e.id]) {
      const r = t[e.id]
      for (const o of r) s.add(o), A(o, t, n, s)
      for (const o of r) B(o, e.parent, s, t, n)
    } else {
      const r = It(e)
      for (const o of r.target)
        s.add(o),
          r === ((i = e.parent) == null ? void 0 : i.initial) &&
            n.add(e.parent),
          A(o, t, n, s)
      for (const o of r.target) B(o, e.parent, s, t, n)
    }
  else if (e.type === 'compound') {
    const [r] = e.initial.target
    T(r) || (s.add(r), n.add(r)), A(r, t, n, s), B(r, e, s, t, n)
  } else if (e.type === 'parallel')
    for (const r of $(e).filter(o => !T(o)))
      [...s].some(o => b(o, r)) || (T(r) || (s.add(r), n.add(r)), A(r, t, n, s))
}
function Mt(e, t, n, s, i) {
  for (const r of s)
    if (((!i || b(r, i)) && e.add(r), r.type === 'parallel'))
      for (const o of $(r).filter(c => !T(c)))
        [...e].some(c => b(c, o)) || (e.add(o), A(o, t, n, e))
}
function B(e, t, n, s, i) {
  Mt(n, s, i, D(e, t))
}
function Se(e, t, n, s, i, r, o) {
  let c = e
  const u = X(s, i, r)
  u.sort((d, h) => h.order - d.order)
  let a
  for (const d of u)
    for (const h of le(d)) {
      let f
      h.history === 'deep'
        ? (f = l => N(l) && b(l, d))
        : (f = l => l.parent === d),
        a ?? (a = {...r}),
        (a[h.id] = Array.from(i).filter(f))
    }
  for (const d of u)
    (c = M(c, t, n, [...d.exit, ...d.invoke.map(h => xt(h.id))], o)),
      i.delete(d)
  return [c, a || r]
}
let ft = !1
function jt(e, t, n, s, i, r) {
  const {machine: o} = e
  let c = e
  for (const a of s) {
    const p = () => {
      n.system._sendInspectionEvent({
        type: '@xstate.action',
        actorRef: n.self,
        action: {
          type:
            typeof a == 'string'
              ? a
              : typeof a == 'object'
                ? a.type
                : a.name || '(anonymous)',
          params: l,
        },
      })
      try {
        ;(ft = h), h(f, l)
      } finally {
        ft = !1
      }
    }
    var u = p
    const d = typeof a == 'function',
      h = d ? a : o.implementations.actions[typeof a == 'string' ? a : a.type]
    if (!h) continue
    const f = {context: c.context, event: t, self: n.self, system: n.system},
      l =
        d || typeof a == 'string'
          ? void 0
          : 'params' in a
            ? typeof a.params == 'function'
              ? a.params({context: c.context, event: t})
              : a.params
            : void 0
    if (!('resolve' in h)) {
      n.self._processingStatus === g.Running
        ? p()
        : n.defer(() => {
            p()
          })
      continue
    }
    const y = h,
      [v, w, m] = y.resolve(n, c, f, l, h, i)
    ;(c = v),
      'retryResolve' in y && (r == null || r.push([y, w])),
      'execute' in y &&
        (n.self._processingStatus === g.Running
          ? y.execute(n, w)
          : n.defer(y.execute.bind(null, n, w))),
      m && (c = jt(c, t, n, m, i, r))
  }
  return c
}
function M(e, t, n, s, i, r) {
  const o = r ? [] : void 0,
    c = jt(e, t, n, s, {internalQueue: i, deferredActorIds: r}, o)
  return (
    o == null ||
      o.forEach(([u, a]) => {
        u.retryResolve(n, c, a)
      }),
    c
  )
}
function U(e, t, n, s = []) {
  let i = e
  const r = []
  function o(a, d, h) {
    n.system._sendInspectionEvent({
      type: '@xstate.microstep',
      actorRef: n.self,
      event: d,
      snapshot: a,
      _transitions: h,
    }),
      r.push(a)
  }
  if (t.type === W)
    return (
      (i = E(dt(i, t, n), {status: 'stopped'})),
      o(i, t, []),
      {snapshot: i, microstates: r}
    )
  let c = t
  if (c.type !== vt) {
    const a = c,
      d = Xt(a),
      h = lt(a, i)
    if (d && !h.length)
      return (
        (i = E(e, {status: 'error', error: a.error})),
        o(i, a, []),
        {snapshot: i, microstates: r}
      )
    ;(i = K(h, e, n, c, !1, s)), o(i, a, h)
  }
  let u = !0
  while (i.status === 'active') {
    let a = u ? be(i, c) : []
    const d = a.length ? i : void 0
    if (!a.length) {
      if (!s.length) break
      ;(c = s.shift()), (a = lt(c, i))
    }
    ;(i = K(a, i, n, c, !1, s)), (u = i !== d), o(i, c, a)
  }
  return i.status !== 'active' && dt(i, c, n), {snapshot: i, microstates: r}
}
function dt(e, t, n) {
  return M(
    e,
    t,
    n,
    Object.values(e.children).map(s => xt(s)),
    [],
  )
}
function lt(e, t) {
  return t.machine.getTransitionData(t, e)
}
function be(e, t) {
  const n = new Set(),
    s = e._nodes.filter(N)
  for (const i of s)
    t: for (const r of [i].concat(D(i, void 0)))
      if (r.always) {
        for (const o of r.always)
          if (o.guard === void 0 || V(o.guard, e.context, t, e)) {
            n.add(o)
            break t
          }
      }
  return $t(Array.from(n), new Set(e._nodes), e.historyValue)
}
function we(e, t) {
  const n = L(G(e, t))
  return kt(e, [...n])
}
function xe(e) {
  return !!e && typeof e == 'object' && 'machine' in e && 'value' in e
}
const Te = function (t) {
    return _t(t, this.value)
  },
  Ee = function (t) {
    return this.tags.has(t)
  },
  ke = function (t) {
    const n = this.machine.getTransitionData(this, t)
    return (
      !!(n != null && n.length) &&
      n.some(s => s.target !== void 0 || s.actions.length)
    )
  },
  Ie = function () {
    const {
      _nodes: t,
      tags: n,
      machine: s,
      getMeta: i,
      toJSON: r,
      can: o,
      hasTag: c,
      matches: u,
      ...a
    } = this
    return {...a, tags: Array.from(n)}
  },
  Ae = function () {
    return this._nodes.reduce(
      (t, n) => (n.meta !== void 0 && (t[n.id] = n.meta), t),
      {},
    )
  }
function R(e, t) {
  return {
    status: e.status,
    output: e.output,
    error: e.error,
    machine: t,
    context: e.context,
    _nodes: e._nodes,
    value: kt(t.root, e._nodes),
    tags: new Set(e._nodes.flatMap(n => n.tags)),
    children: e.children,
    historyValue: e.historyValue || {},
    matches: Te,
    hasTag: Ee,
    can: ke,
    getMeta: Ae,
    toJSON: Ie,
  }
}
function E(e, t = {}) {
  return R({...e, ...t}, e.machine)
}
function $e(e, t) {
  const {
      _nodes: n,
      tags: s,
      machine: i,
      children: r,
      context: o,
      can: c,
      hasTag: u,
      matches: a,
      getMeta: d,
      toJSON: h,
      ...f
    } = e,
    l = {}
  for (const y in r) {
    const v = r[y]
    l[y] = {
      snapshot: v.getPersistedSnapshot(t),
      src: v.src,
      systemId: v._systemId,
      syncSnapshot: v._syncSnapshot,
    }
  }
  return {...f, context: Ct(o), children: l}
}
function Ct(e) {
  let t
  for (const n in e) {
    const s = e[n]
    if (s && typeof s == 'object')
      if ('sessionId' in s && 'send' in s && 'ref' in s)
        t ?? (t = Array.isArray(e) ? e.slice() : {...e}),
          (t[n] = {xstate$$type: Q, id: s.id})
      else {
        const i = Ct(s)
        i !== s &&
          (t ?? (t = Array.isArray(e) ? e.slice() : {...e}), (t[n] = i))
      }
  }
  return t ?? e
}
function Oe(e, t, n, s, {event: i, id: r, delay: o}, {internalQueue: c}) {
  const u = t.machine.implementations.delays
  if (typeof i == 'string')
    throw new Error(
      `Only event objects may be used with raise; use raise({ type: "${i}" }) instead`,
    )
  const a = typeof i == 'function' ? i(n, s) : i
  let d
  if (typeof o == 'string') {
    const h = u && u[o]
    d = typeof h == 'function' ? h(n, s) : h
  } else d = typeof o == 'function' ? o(n, s) : o
  return typeof d != 'number' && c.push(a), [t, {event: a, id: r, delay: d}]
}
function Me(e, t) {
  const {event: n, delay: s, id: i} = t
  if (typeof s == 'number') {
    e.defer(() => {
      const r = e.self
      e.system.scheduler.schedule(r, r, n, s, i)
    })
    return
  }
}
function je(e, t) {
  function n(s, i) {}
  return (
    (n.type = 'xstate.raise'),
    (n.event = e),
    (n.id = t == null ? void 0 : t.id),
    (n.delay = t == null ? void 0 : t.delay),
    (n.resolve = Oe),
    (n.execute = Me),
    n
  )
}
function Ce(e, {machine: t, context: n}, s, i) {
  const r = (o, c = {}) => {
    const {systemId: u, input: a} = c
    if (typeof o == 'string') {
      const d = F(t, o)
      if (!d)
        throw new Error(
          `Actor logic '${o}' not implemented in machine '${t.id}'`,
        )
      const h = C(d, {
        id: c.id,
        parent: e.self,
        syncSnapshot: c.syncSnapshot,
        input:
          typeof a == 'function' ? a({context: n, event: s, self: e.self}) : a,
        src: o,
        systemId: u,
      })
      return (i[h.id] = h), h
    } else
      return C(o, {
        id: c.id,
        parent: e.self,
        syncSnapshot: c.syncSnapshot,
        input: c.input,
        src: o,
        systemId: u,
      })
  }
  return (o, c) => {
    const u = r(o, c)
    return (
      (i[u.id] = u),
      e.defer(() => {
        u._processingStatus !== g.Stopped && u.start()
      }),
      u
    )
  }
}
function De(e, t, n, s, {assignment: i}) {
  if (!t.context)
    throw new Error(
      'Cannot assign to undefined `context`. Ensure that `context` is defined in the machine config.',
    )
  const r = {},
    o = {
      context: t.context,
      event: n.event,
      spawn: Ce(e, t, n.event, r),
      self: e.self,
      system: e.system,
    }
  let c = {}
  if (typeof i == 'function') c = i(o, s)
  else
    for (const a of Object.keys(i)) {
      const d = i[a]
      c[a] = typeof d == 'function' ? d(o, s) : d
    }
  const u = Object.assign({}, t.context, c)
  return [
    E(t, {
      context: u,
      children: Object.keys(r).length ? {...t.children, ...r} : t.children,
    }),
  ]
}
function P(e) {
  function t(n, s) {}
  return (t.type = 'xstate.assign'), (t.assignment = e), (t.resolve = De), t
}
const pt = new WeakMap()
function k(e, t, n) {
  let s = pt.get(e)
  return s ? t in s || (s[t] = n()) : ((s = {[t]: n()}), pt.set(e, s)), s[t]
}
const Re = {},
  j = e =>
    typeof e == 'string'
      ? {type: e}
      : typeof e == 'function'
        ? 'resolve' in e
          ? {type: e.type}
          : {type: e.name}
        : e
class st {
  constructor(t, n) {
    if (
      ((this.config = t),
      (this.key = void 0),
      (this.id = void 0),
      (this.type = void 0),
      (this.path = void 0),
      (this.states = void 0),
      (this.history = void 0),
      (this.entry = void 0),
      (this.exit = void 0),
      (this.parent = void 0),
      (this.machine = void 0),
      (this.meta = void 0),
      (this.output = void 0),
      (this.order = -1),
      (this.description = void 0),
      (this.tags = []),
      (this.transitions = void 0),
      (this.always = void 0),
      (this.parent = n._parent),
      (this.key = n._key),
      (this.machine = n._machine),
      (this.path = this.parent ? this.parent.path.concat(this.key) : []),
      (this.id = this.config.id || [this.machine.id, ...this.path].join(yt)),
      (this.type =
        this.config.type ||
        (this.config.states && Object.keys(this.config.states).length
          ? 'compound'
          : this.config.history
            ? 'history'
            : 'atomic')),
      (this.description = this.config.description),
      (this.order = this.machine.idMap.size),
      this.machine.idMap.set(this.id, this),
      (this.states = this.config.states
        ? ct(
            this.config.states,
            (s, i) =>
              new st(s, {_parent: this, _key: i, _machine: this.machine}),
          )
        : Re),
      this.type === 'compound' && !this.config.initial)
    )
      throw new Error(
        `No initial state specified for compound state node "#${this.id}". Try adding { initial: "${Object.keys(this.states)[0]}" } to the state config.`,
      )
    ;(this.history =
      this.config.history === !0 ? 'shallow' : this.config.history || !1),
      (this.entry = S(this.config.entry).slice()),
      (this.exit = S(this.config.exit).slice()),
      (this.meta = this.config.meta),
      (this.output =
        this.type === 'final' || !this.parent ? this.config.output : void 0),
      (this.tags = S(t.tags).slice())
  }
  _initialize() {
    ;(this.transitions = ce(this)),
      this.config.always &&
        (this.always = I(this.config.always).map(t => x(this, gt, t))),
      Object.keys(this.states).forEach(t => {
        this.states[t]._initialize()
      })
  }
  get definition() {
    return {
      id: this.id,
      key: this.key,
      version: this.machine.version,
      type: this.type,
      initial: this.initial
        ? {
            target: this.initial.target,
            source: this,
            actions: this.initial.actions.map(j),
            eventType: null,
            reenter: !1,
            toJSON: () => ({
              target: this.initial.target.map(t => `#${t.id}`),
              source: `#${this.id}`,
              actions: this.initial.actions.map(j),
              eventType: null,
            }),
          }
        : void 0,
      history: this.history,
      states: ct(this.states, t => t.definition),
      on: this.on,
      transitions: [...this.transitions.values()]
        .flat()
        .map(t => ({...t, actions: t.actions.map(j)})),
      entry: this.entry.map(j),
      exit: this.exit.map(j),
      meta: this.meta,
      order: this.order || -1,
      output: this.output,
      invoke: this.invoke,
      description: this.description,
      tags: this.tags,
    }
  }
  toJSON() {
    return this.definition
  }
  get invoke() {
    return k(this, 'invoke', () =>
      S(this.config.invoke).map((t, n) => {
        const {src: s, systemId: i} = t,
          r = t.id ?? at(this.id, n),
          o = typeof s == 'string' ? s : `xstate.invoke.${at(this.id, n)}`
        return {
          ...t,
          src: o,
          id: r,
          systemId: i,
          toJSON() {
            const {onDone: c, onError: u, ...a} = t
            return {...a, type: 'xstate.invoke', src: o, id: r}
          },
        }
      }),
    )
  }
  get on() {
    return k(this, 'on', () =>
      [...this.transitions]
        .flatMap(([n, s]) => s.map(i => [n, i]))
        .reduce((n, [s, i]) => ((n[s] = n[s] || []), n[s].push(i), n), {}),
    )
  }
  get after() {
    return k(this, 'delayedTransitions', () => oe(this))
  }
  get initial() {
    return k(this, 'initial', () => ae(this, this.config.initial))
  }
  next(t, n) {
    const s = n.type,
      i = []
    let r
    const o = k(this, `candidates-${s}`, () => re(this, s))
    for (const c of o) {
      const {guard: u} = c,
        a = t.context
      let d = !1
      try {
        d = !u || V(u, a, n, t)
      } catch (h) {
        const f =
          typeof u == 'string' ? u : typeof u == 'object' ? u.type : void 0
        throw new Error(`Unable to evaluate guard ${f ? `'${f}' ` : ''}in transition for event '${s}' in state node '${this.id}':
${h.message}`)
      }
      if (d) {
        i.push(...c.actions), (r = c)
        break
      }
    }
    return r ? [r] : void 0
  }
  get events() {
    return k(this, 'events', () => {
      const {states: t} = this,
        n = new Set(this.ownEvents)
      if (t)
        for (const s of Object.keys(t)) {
          const i = t[s]
          if (i.states) for (const r of i.events) n.add(`${r}`)
        }
      return Array.from(n)
    })
  }
  get ownEvents() {
    const t = new Set(
      [...this.transitions.keys()].filter(n =>
        this.transitions
          .get(n)
          .some(s => !(!s.target && !s.actions.length && !s.reenter)),
      ),
    )
    return Array.from(t)
  }
}
const Le = '#'
class it {
  constructor(t, n) {
    ;(this.config = t),
      (this.version = void 0),
      (this.schemas = void 0),
      (this.implementations = void 0),
      (this.__xstatenode = !0),
      (this.idMap = new Map()),
      (this.root = void 0),
      (this.id = void 0),
      (this.states = void 0),
      (this.events = void 0),
      (this.id = t.id || '(machine)'),
      (this.implementations = {
        actors: (n == null ? void 0 : n.actors) ?? {},
        actions: (n == null ? void 0 : n.actions) ?? {},
        delays: (n == null ? void 0 : n.delays) ?? {},
        guards: (n == null ? void 0 : n.guards) ?? {},
      }),
      (this.version = this.config.version),
      (this.schemas = this.config.schemas),
      (this.transition = this.transition.bind(this)),
      (this.getInitialSnapshot = this.getInitialSnapshot.bind(this)),
      (this.getPersistedSnapshot = this.getPersistedSnapshot.bind(this)),
      (this.restoreSnapshot = this.restoreSnapshot.bind(this)),
      (this.start = this.start.bind(this)),
      (this.root = new st(t, {_key: this.id, _machine: this})),
      this.root._initialize(),
      (this.states = this.root.states),
      (this.events = this.root.events)
  }
  provide(t) {
    const {actions: n, guards: s, actors: i, delays: r} = this.implementations
    return new it(this.config, {
      actions: {...n, ...t.actions},
      guards: {...s, ...t.guards},
      actors: {...i, ...t.actors},
      delays: {...r, ...t.delays},
    })
  }
  resolveState(t) {
    const n = we(this.root, t.value),
      s = L(G(this.root, n))
    return R(
      {
        _nodes: [...s],
        context: t.context || {},
        children: {},
        status: tt(s, this.root) ? 'done' : t.status || 'active',
        output: t.output,
        error: t.error,
        historyValue: t.historyValue,
      },
      this,
    )
  }
  transition(t, n, s) {
    return U(t, n, s).snapshot
  }
  microstep(t, n, s) {
    return U(t, n, s).microstates
  }
  getTransitionData(t, n) {
    return et(this.root, t.value, t, n) || []
  }
  getPreInitialState(t, n, s) {
    const {context: i} = this.config,
      r = R(
        {
          context: typeof i != 'function' && i ? i : {},
          _nodes: [this.root],
          children: {},
          status: 'active',
        },
        this,
      )
    return typeof i == 'function'
      ? M(
          r,
          n,
          t,
          [
            P(({spawn: c, event: u, self: a}) =>
              i({spawn: c, input: u.input, self: a}),
            ),
          ],
          s,
        )
      : r
  }
  getInitialSnapshot(t, n) {
    const s = mt(n),
      i = [],
      r = this.getPreInitialState(t, s, i),
      o = K(
        [
          {
            target: [...At(this.root)],
            source: this.root,
            reenter: !0,
            actions: [],
            eventType: null,
            toJSON: null,
          },
        ],
        r,
        t,
        s,
        !0,
        i,
      ),
      {snapshot: c} = U(o, s, t, i)
    return c
  }
  start(t) {
    Object.values(t.children).forEach(n => {
      n.getSnapshot().status === 'active' && n.start()
    })
  }
  getStateNodeById(t) {
    const n = Z(t),
      s = n.slice(1),
      i = z(n[0]) ? n[0].slice(Le.length) : n[0],
      r = this.idMap.get(i)
    if (!r)
      throw new Error(
        `Child state node '#${i}' does not exist on machine '${this.id}'`,
      )
    return J(r, s)
  }
  get definition() {
    return this.root.definition
  }
  toJSON() {
    return this.definition
  }
  getPersistedSnapshot(t, n) {
    return $e(t, n)
  }
  restoreSnapshot(t, n) {
    const s = {},
      i = t.children
    Object.keys(i).forEach(u => {
      const a = i[u],
        d = a.snapshot,
        h = a.src,
        f = typeof h == 'string' ? F(this, h) : h
      if (!f) return
      const l = C(f, {
        id: u,
        parent: n.self,
        syncSnapshot: a.syncSnapshot,
        snapshot: d,
        src: h,
        systemId: a.systemId,
      })
      s[u] = l
    })
    const r = R(
      {...t, children: s, _nodes: Array.from(L(G(this.root, t.value)))},
      this,
    )
    const o = new Set()
    function c(u, a) {
      if (!o.has(u)) {
        o.add(u)
        for (const d in u) {
          const h = u[d]
          if (h && typeof h == 'object') {
            if ('xstate$$type' in h && h.xstate$$type === Q) {
              u[d] = a[h.id]
              continue
            }
            c(h, a)
          }
        }
      }
    }
    return c(r.context, s), r
  }
}
function Je(e, t) {
  return new it(e, t)
}
function Ge({schemas: e, actors: t, actions: n, guards: s, delays: i}) {
  return {
    createMachine: r =>
      Je({...r, schemas: e}, {actors: t, actions: n, guards: s, delays: i}),
  }
}
const ze = 'saml'
document.addEventListener('DOMContentLoaded', () => {
  const e = document.body.querySelector('.js-kleinweb-auth-idp-toggle-button')
  if (!(e instanceof HTMLElement)) {
    console.error('[kleinweb-auth]: Toggle button not found!', {value: e})
    return
  }
  const t = Ge({types: {context: {}}}).createMachine({
      id: 'toggle',
      initial: ze,
      context: () => ({buttonText: e.innerHTML.trim()}),
      states: {
        saml: {
          on: {TOGGLE: {target: 'local'}},
          exit: P({buttonText: 'Log in with TU AccessNet'}),
        },
        local: {
          on: {TOGGLE: {target: 'saml'}},
          exit: P({buttonText: 'Use local account'}),
        },
      },
    }),
    n = C(t)
  n.subscribe(s => {
    const {context: i} = s
    ;(document.body.dataset.kleinwebAuthIdp = s.value),
      (e.textContent = i.buttonText)
  }),
    n.start(),
    e.addEventListener('click', s => {
      const i = n.getSnapshot()
      s.preventDefault(), n.send({type: 'TOGGLE', prevContext: i.context})
    })
})
//# sourceMappingURL=kleinweb-auth-login-BBi5ukQ4.js.map
