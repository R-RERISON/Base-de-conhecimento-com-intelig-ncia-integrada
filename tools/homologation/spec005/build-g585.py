#!/usr/bin/env python3
"""Deterministic homologation builder for SPEC-005 / G-585 v2."""

from __future__ import annotations
import argparse, hashlib, json, re, shutil, subprocess, sys, tempfile
from pathlib import Path

PLUGIN_REL=Path("plugin/base-conhecimento-inteligencia-integrada")
BOOTSTRAP="base-conhecimento-inteligencia-integrada.php"
VERSION="0.5.1-rc.14"
BUILD_LABEL="g585.4"

FLAGS_FALSE=(
"BDC_KB_SPEC004_PROFILE_BUILD","BDC_KB_SPEC004_G220_SMOKE_BUILD","BDC_KB_SPEC004_G230_SMOKE_BUILD",
"BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD","BDC_KB_SPEC004_KD_V2_SMOKE_BUILD","BDC_KB_SPEC004_FINAL_DIAG_BUILD",
"BDC_KB_SPEC004_PIPELINE_DIAG_BUILD","BDC_KB_SPEC004_G245_PREFLIGHT_BUILD","BDC_KB_SPEC004_G245_PROJECTION_SMOKE_BUILD",
"BDC_KB_SPEC004_G245_JOURNAL_SMOKE_BUILD","BDC_KB_SPEC004_G245_BLOCK_PROJECTION_SMOKE_BUILD",
"BDC_KB_SPEC004_G245_EDITORIAL_FIDELITY_SMOKE_BUILD","BDC_KB_SPEC004_G245_LOSSLESS_ROUNDTRIP_SMOKE_BUILD",
"BDC_KB_SPEC004_G245_EDITORIAL_PARITY_SMOKE_BUILD","BDC_KB_SPEC004_G245_BLOCK_MIGRATION_READINESS_SMOKE_BUILD",
"BDC_KB_SPEC004_G245_STORAGE_LOCK_SMOKE_BUILD","BDC_KB_SPEC004_G245_AUTHORIZATION_PACK_SMOKE_BUILD",
"BDC_KB_SPEC004_G245_T099C_CANARY_BUILD","BDC_KB_SPEC004_G245_T100A_BATCH_AUTHORIZATION_PACK_BUILD",
"BDC_KB_SPEC004_G245_T100D_CORE_BLOCKS_EXECUTOR_BUILD","BDC_KB_SPEC004_T100E_E6_REGRESSION_BUILD",
"BDC_KB_SPEC004_G250_LIFECYCLE_BUILD","BDC_KB_SPEC005_R500_DIAGNOSTIC_BUILD","BDC_KB_SPEC005_R510_LEGACY_GOLDEN_BUILD",
"BDC_KB_SPEC005_R510_GOLDEN_BASELINE_BUILD","BDC_KB_SPEC005_R510_GOLDEN_AUTO_VALIDATOR_BUILD",
"BDC_KB_SPEC005_G540_CORPUS_RUNNER_BUILD","BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD",
"BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD","BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD",
"BDC_KB_SPEC005_G580_LIFECYCLE_BUILD","BDC_KB_SPEC005_G590_SECTION_BUILD","BDC_KB_P580_PUBLIC_INVENTORY_BUILD",
"BDC_KB_UX004_H030_TECHNICAL_BUILD",
)
FLAGS_TRUE=(
"BDC_KB_SPEC004_G245_T100A_POST_WORKSPACE_BUILD","BDC_KB_SPEC004_G245_T100C_CORE_BLOCKS_ACTIVITY_BUILD",
"BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD","BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD",
"BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD","BDC_KB_WORD_CLOUD_BUILD",
)
REQUIRED=(
"includes/class-search-independence-runner-g585.php","includes/class-golden-suite-loader.php",
"includes/class-golden-gate-runner-g550.php","resources/search/golden-relevance-v1.0.0.json",
"resources/search/technical-challenge-v1.0.0.json","templates/public-home-preview.php",
"templates/public-article-preview.php","uninstall.php",
)
FORBIDDEN=(
"includes/class-search-section-runner-g590.php","includes/class-search-security-performance-runner-g570.php",
"includes/class-search-lifecycle-runner-g580.php","includes/class-public-home-technical-runner-h030.php",
"includes/class-public-experience-inventory-runner-p580.php",
)
TESTS=(
"tests/unit/spec005-g585-decommission-readiness-v2.php","tests/unit/spec005-search-engine.php",
"tests/unit/spec005-golden-gate.php","tests/unit/spec005-runtime-resources.php",
"tests/unit/spec005-section-retrieval-g590.php",
)

def run(cmd,cwd): return subprocess.run(cmd,cwd=cwd,stdout=subprocess.PIPE,stderr=subprocess.STDOUT,text=True,check=False)
def need(label,r):
    if r.returncode: raise RuntimeError(label+" failed\n"+r.stdout)
    return r.stdout
def sha(path): return hashlib.sha256(path.read_bytes()).hexdigest()
def set_flag(src,name,val):
    p=re.compile(r"(define\(\s*['\"]"+re.escape(name)+r"['\"]\s*,\s*)(true|false)(\s*\)\s*;)",re.I)
    out,n=p.subn(r"\1"+("true" if val else "false")+r"\3",src,count=1)
    if n!=1: raise RuntimeError("flag not found exactly once: "+name)
    return out
def patch(path,build_id):
    s=path.read_text(encoding="utf-8")
    s,n=re.subn(r"(?m)^ \* Version:\s*.+$",f" * Version: {VERSION}",s,count=1)
    if n!=1: raise RuntimeError("header version missing")
    s,n=re.subn(r"define\(\s*'BDC_KB_VERSION'\s*,\s*'[^']+'\s*\);",f"define( 'BDC_KB_VERSION', '{VERSION}' );",s,count=1)
    if n!=1: raise RuntimeError("version constant missing")
    if "BDC_KB_BUILD_ID" in s: raise RuntimeError("persistent build id present in source")
    s=s.replace(f"define( 'BDC_KB_VERSION', '{VERSION}' );",f"define( 'BDC_KB_VERSION', '{VERSION}' );\ndefine( 'BDC_KB_BUILD_ID', '{build_id}' );",1)
    for x in FLAGS_FALSE: s=set_flag(s,x,False)
    for x in FLAGS_TRUE: s=set_flag(s,x,True)
    path.write_text(s,encoding="utf-8")
    return {"version":VERSION,"build_id":build_id,"false_flags":list(FLAGS_FALSE),"true_flags":list(FLAGS_TRUE)}
def lint_tree(root):
    php=shutil.which("php")
    if not php: raise RuntimeError("php CLI required")
    files=sorted(root.rglob("*.php")); fail=[]
    for p in files:
        r=run([php,"-l",str(p)],root)
        if r.returncode: fail.append({"file":str(p.relative_to(root)),"output":r.stdout})
    if fail: raise RuntimeError(json.dumps(fail,ensure_ascii=False))
    return {"checked":len(files),"failed":0}
def main():
    ap=argparse.ArgumentParser(); ap.add_argument("--root",default="."); ap.add_argument("--output",default=f"dist/base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD_LABEL}.zip"); ap.add_argument("--manifest",default=f"dist/base-conhecimento-inteligencia-integrada-{VERSION}-{BUILD_LABEL}.manifest.json"); ap.add_argument("--evidence",default="dist/g585-v2-local-package-validation.json"); a=ap.parse_args()
    repo=Path(a.root).resolve(); plugin=repo/PLUGIN_REL; boot=plugin/BOOTSTRAP; release=repo/"tools/t100e/build_release.py"; regress=repo/"tools/t100e/regression_runner.py"
    for p in (boot,release,regress):
        if not p.is_file(): raise SystemExit("missing: "+str(p))
    git=shutil.which("git"); php=shutil.which("php")
    if not git or not php: raise SystemExit("git/php required")
    head=need("git head",run([git,"rev-parse","HEAD"],repo)).strip()
    dirty=need("git status",run([git,"status","--porcelain"],repo)).strip()
    if dirty: raise SystemExit("refusing dirty tree")
    branch=need("git branch",run([git,"rev-parse","--abbrev-ref","HEAD"],repo)).strip()
    build_id=f"{BUILD_LABEL}-{head[:12]}"
    gates={}
    for rel in TESTS:
        r=run([php,str(repo/rel)],repo); gates[rel]={"returncode":r.returncode,"output":r.stdout}; need(rel,r)
    rr=run([sys.executable,str(regress),"--root",str(repo)],repo); gates["t100e_regression"]={"returncode":rr.returncode,"output":rr.stdout}; need("T100E",rr)
    with tempfile.TemporaryDirectory(prefix="bdc-g585-") as td:
        stage=Path(td)/"repo"; sp=stage/PLUGIN_REL; st=stage/"tools/t100e"; sp.parent.mkdir(parents=True); st.mkdir(parents=True)
        shutil.copytree(plugin,sp); shutil.copy2(release,st/"build_release.py"); shutil.copy2(regress,st/"regression_runner.py")
        profile=patch(sp/BOOTSTRAP,build_id); source_lint=lint_tree(sp)
        out=Path(a.output).resolve(); man=Path(a.manifest).resolve(); out.parent.mkdir(parents=True,exist_ok=True)
        z1=Path(td)/"1.zip"; z2=Path(td)/"2.zip"; m1=Path(td)/"1.json"; m2=Path(td)/"2.json"
        base=[sys.executable,str(st/"build_release.py"),"--root",str(stage)]
        need("build1",run(base+["--output",str(z1),"--manifest",str(m1)],stage)); need("build2",run(base+["--output",str(z2),"--manifest",str(m2)],stage))
        if z1.read_bytes()!=z2.read_bytes(): raise RuntimeError("deterministic build mismatch")
        shutil.copy2(z1,out); data=json.loads(m1.read_text(encoding="utf-8")); shutil.copy2(m1,man)
        inc=set((data.get("included_files") or {}).keys()); missing=sorted(set(REQUIRED)-inc); forbidden=sorted(set(FORBIDDEN)&inc)
        if missing or forbidden: raise RuntimeError(json.dumps({"missing":missing,"forbidden":forbidden}))
        with tempfile.TemporaryDirectory(prefix="bdc-g585-unzip-") as ud:
            import zipfile
            with zipfile.ZipFile(out) as zf: zf.extractall(ud)
            zip_lint=lint_tree(Path(ud))
    evidence={"schema_version":"1.0.0","gate":"G-585-v2-PACKAGE","mode":"local_deterministic_homologation_build","plugin_version":VERSION,"build_id":build_id,"source":{"head":head,"branch":branch,"dirty":False},"local_gates":gates,"source_lint":source_lint,"zip_lint":zip_lint,"artifact_contract":{"required":list(REQUIRED),"forbidden":list(FORBIDDEN),"pass":True},"artifact":{"path":str(out),"sha256":sha(out),"manifest":str(man),"deterministic_equal":True},"build_profile":profile,"pass":True}
    ep=Path(a.evidence).resolve(); ep.parent.mkdir(parents=True,exist_ok=True); ep.write_text(json.dumps(evidence,ensure_ascii=False,indent=2,sort_keys=True)+"\n",encoding="utf-8"); print(json.dumps(evidence,ensure_ascii=False,indent=2,sort_keys=True)); return 0
if __name__=="__main__": raise SystemExit(main())
