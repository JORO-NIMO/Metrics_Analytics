
## Maternal Health Uganda 



## What This Lecture Is About

Chapter 6 measures how complex the **internal structure** of software is — not just how long it is. Three structural dimensions are covered:

```
Structural Complexity
├── Code Level
│   ├── Cyclomatic Complexity  → How many independent paths through a function?
│   └── Data Structure         → How complex are the data types used?
└── Architecture Level
    ├── Cohesion               → How focused is each module?
    ├── Coupling               → How dependent are modules on each other?
    ├── Information Flow       → How much data flows in and out?
    └── Architecture Morphology → What shape is the overall system?
```

---

## 1 — Cyclomatic Complexity

### What it is
Measures the number of independent paths through a program. The more decision points (if, while, for, case, catch), the more complex the code.

### Formula (two equivalent ways)

**Flowgraph-based:** `v(G) = e − n + 2p`
- e = number of edges, n = number of nodes, p = connected components

**Code-based (simpler):** `v(G) = 1 + d`
- d = number of decision points (if / elseif / while / for / foreach / case / catch)

### Risk scale

| v(G) | Risk Level | Meaning |
|---|---|---|
| 1 – 10 | Low | Simple, easy to test |
| 11 – 20 | Moderate | More complex, manageable |
| 21 – 50 | High | Very complex, difficult to test |
| 51+ | Very High | Untestable without refactoring |

### Our results (measured from source)

| File | Decision Points (d) | v(G) | Risk |
|---|---|---|---|
| metrics_logger.php | 17 | 18 | Moderate |
| signup.php | 9 | 10 | Low |
| savetracker.php | 8 | 9 | Low |
| computeresults.php | 4 | 5 | Low |
| login.php | 4 | 5 | Low |
| (all others) | 0–5 | 1–6 | Low |

**Finding:** Only `metrics_logger.php` reaches moderate risk. All other files are low. This is good — it means the codebase is testable and maintainable.

**Table:** `cyclomatic_complexity` | **Dashboard:**  Complexity → Cyclomatic

---

## 2 — Cohesion

Cohesion measures how focused a module is — does it do one thing, or many unrelated things?

**Goal: High cohesion.** A highly cohesive module works mostly within itself.

### Formula
`CH(C) = internal_relations / (internal_relations + external_relations)`

`System Cohesion = mean of all module cohesion values`

### Cohesion types (best to worst)

| Type | Meaning |
|---|---|
| Functional | Module performs exactly one function |
| Sequential | Output of one part feeds the next |
| Communicative | Multiple functions on the same data |
| Procedural | Steps of a procedure |
| Temporal | Functions run at the same time |
| Logical | Functions are logically related |
| Coincidental | Functions have no relationship at all |

### Our results

| Module | Internal | External | Cohesion % | Type |
|---|---|---|---|---|
| Config | 2 | 0 | 100.00% | Functional |
| Authentication | 6 | 2 | 75.00% | Functional |
| Tracker | 8 | 3 | 72.73% | Functional |
| Metrics Logger | 18 | 8 | 69.23% | Communicative |
| Survey | 4 | 2 | 66.67% | Sequential |
| Admin APIs | 10 | 5 | 66.67% | Communicative |
| Content | 6 | 4 | 60.00% | Sequential |

**System Cohesion = 72.88%** — Good. All modules do more work internally than externally.

**Table:** `cohesion_metrics` | **Dashboard:**  Complexity → Cohesion

---

## 3 — Coupling

### What it is
Coupling measures how dependent modules are on each other.

**Goal: Loose coupling.** Modules should be as independent as possible.

### Formula
`c(x, y) = coupling_rank + n / (n + 1)`

Where coupling_rank comes from the type (R0–R4) and n is the number of interconnections.

### Coupling types

| Type | Name | Description |
|---|---|---|
| R0 | Independence | No communication between modules |
| R1 | Data coupling | Modules communicate via parameters only |
| R2 | Stamp coupling | Modules share the same record/object type |
| R3 | Control coupling | One module passes a flag to control another |
| R4 | Content coupling | One module directly accesses internals of another |

**Loose = R0, R1 | Tight = R3, R4**

### Global coupling = median of all c(x,y) values

### Our results (selected pairs)

| Module X | Module Y | Type | c(x,y) |
|---|---|---|---|
| config.php | login.php | R1 | 1.5000 |
| config.php | signup.php | R1 | 1.5000 |
| metrics_logger.php | login.php | R1 | 1.6667 |
| metrics_logger.php | savetracker.php | R2 | 2.7500 |
| savetracker.php | computeresults.php | R2 | 2.6667 |

**Global System Coupling = 1.6667** — Good. Predominantly R1 (data coupling = loose).

**Table:** `coupling_metrics` | **Dashboard:**  Complexity → Coupling

---

## 4 — Information Flow (Fan-In / Fan-Out)

### What it is
Measures how much data moves into and out of each module.

- **Fan-in(M)** = number of flows arriving at M + data structures M reads from
- **Fan-out(M)** = number of flows leaving M + data structures M writes to
- **IFC(M) = (fan-in × fan-out)²**

High IFC means a module is both widely used and widely dependent — a potential bottleneck.

### Our results

| Module | Fan-In | Fan-Out | IFC |
|---|---|---|---|
| metrics_logger.php | 7 | 6 | 1764 |
| savetracker.php | 3 | 4 | 144 |
| login.php | 2 | 3 | 36 |
| signup.php | 2 | 3 | 36 |
| config.php | 0 | 8 | 0 |

**Finding:** `metrics_logger.php` has the highest IFC (1764) because it is called by many modules and writes to many tables. This is expected — it is the central logging hub.

**Table:** `information_flow` | **Dashboard:**  Complexity → Information Flow

---

## 5 — Architecture Metrics

The overall shape of the system described as a graph S = {N, R}.

| Metric | Description |
|---|---|
| Nodes | Number of backend modules |
| Edges | Number of relationships between modules |
| Depth | Longest path from root (config) to a leaf |
| Width | Maximum modules at any single layer |
| Edge-to-node ratio | e/n — connectivity density |
| Impurity m(G) | 2(e − n + 1) / ((n−1)(n−2)) — lower is better |

### Our architecture

| Metric | Value |
|---|---|
| Nodes | 15 |
| Edges | 28 |
| Depth | 3 |
| Width | 8 |
| Edge/Node ratio | 1.8667 |
| Impurity m(G) | 0.142857 |

**Three layers:**
1. Foundation: `config.php`
2. Core logic: `metrics_logger.php`, `savetracker.php`, `login.php`, `signup.php` …
3. Endpoints: `getmetrics.php`, `getexperiments.php`, `submitsurvey.php` …

**Table:** `architecture_metrics` | **Dashboard:** Complexity → Architecture

---

## 6 — Data Structure Complexity

### What it is
Measures complexity based on the data types used in each file.

### Formula
`C = (ni × 1) + (ns × 2) + (na × 2 × array_size)`

Where ni = integer variables, ns = string variables, na = array variables.

**Trade-off:** Programs with higher cyclomatic complexity usually have simpler data structures, and vice versa.

### Our results

| File | ni | ns | na | C1 | C2 | C3 | Total C |
|---|---|---|---|---|---|---|---|
| metrics_logger.php | 8 | 12 | 3 | 8 | 24 | 36 | 68 |
| getmetrics.php | 2 | 2 | 2 | 2 | 4 | 40 | 46 |
| computeresults.php | 5 | 2 | 3 | 5 | 4 | 30 | 39 |
| savetracker.php | 6 | 3 | 2 | 6 | 6 | 16 | 28 |

**Observation:** `getmetrics.php` has low cyclomatic complexity (v=3) but high data structure complexity (C=46) due to large result arrays — confirming the trade-off the lecture describes.

**Table:** `data_structure_complexity` | **Dashboard:** 🔀 Complexity → Data Structure

---

## Files Added

| Item | Type | Purpose |
|---|---|---|
| `cyclomatic_complexity` | DB Table | v(G) per file with risk level |
| `cohesion_metrics` | DB Table | CH(C) per module |
| `coupling_metrics` | DB Table | c(x,y) per module pair |
| `information_flow` | DB Table | Fan-in, fan-out, IFC per module |
| `architecture_metrics` | DB Table | System-level morphology |
| `data_structure_complexity` | DB Table | C = C1+C2+C3 per file |
| `backend/getcomplexity.php` | PHP API | Serves all complexity data to dashboard |
| Dashboard tab 🔀 Complexity | HTML/JS | Displays all six complexity measures |
