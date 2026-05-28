// order-suggestions-local.js
function currentMealNames() {
  var names = {};

  cart.forEach(function (item) {
    names[normalizeText(item.name)] = true;
  });

  document.querySelectorAll(".orders-list .o-name").forEach(function (node) {
    names[normalizeText(node.textContent)] = true;
  });

  return names;
}

function itemAlreadyChosen(item, chosenNames) {
  return !!chosenNames[normalizeText(menuItemName(item))];
}

function activeSuggestMode() {
  var active = document.querySelector("[data-suggest-mode].active");
  return active ? active.getAttribute("data-suggest-mode") : "balanced";
}

function activeSuggestStage() {
  var context = mealContext();
  return context.hasChosen ? "next" : "start";
}

function suggestPeopleCount() {
  var el = document.getElementById("suggestPeople");
  return Math.max(1, parseInt(el && el.value ? el.value : "2", 10) || 2);
}

function suggestIntentText() {
  var el = document.getElementById("suggestIntent");
  return normalizeText(el && el.value ? el.value : "");
}

function parseSuggestIntent() {
  var text = suggestIntentText();
  var negative = [];
  var positive = [];
  var dislikeMarkers = ["khong ", "ko ", "k ", "it ", "han che ", "bo "];
  var preferenceWords = {
    lau: ["lau", "nuoc lau"],
    rau: ["rau", "goi", "salad"],
    nam: ["nam"],
    dauhu: ["dau hu", "tau hu"],
    nhanh: ["nhanh", "ra nhanh", "cho lau"],
    nong: ["nong", "am", "sup", "canh"],
    nhe: ["nhe", "thanh", "it dau"],
    cay: ["cay"],
    chien: ["chien"],
    no: ["no", "chac bung", "com", "mi", "bun"],
    ngot: ["ngot", "trang mieng", "che"],
  };

  Object.keys(preferenceWords).forEach(function (key) {
    var words = preferenceWords[key];
    var mentioned = hasWord(text, words);
    if (!mentioned) return;
    var isNegative = dislikeMarkers.some(function (marker) {
      return words.some(function (word) {
        return text.indexOf(marker + word) !== -1;
      });
    });
    (isNegative ? negative : positive).push(key);
  });

  return { raw: text, positive: positive, negative: negative };
}

function hasWord(text, words) {
  return words.some(function (word) {
    return text.indexOf(word) !== -1;
  });
}

function itemSignals(item) {
  var text = menuItemText(item);
  var cat = normalizeText(item.category || item.danh_muc || "");
  return {
    text: text,
    category: item.category || item.danh_muc || "Khác",
    starter: hasWord(text + " " + cat, ["khai vi", "goi", "salad", "cha gio"]),
    soup: hasWord(text + " " + cat, ["sup", "canh", "nuoc lau", "lau"]),
    main: hasWord(text + " " + cat, ["mon chinh", "com", "mi", "bun"]),
    green: hasWord(text + " " + cat, ["rau", "goi", "salad"]),
    topping: hasWord(text + " " + cat, [
      "topping",
      "nam",
      "dau hu",
      "tau hu",
      "vien",
    ]),
    drink: hasWord(text + " " + cat, ["do uong", "tra", "nuoc", "ep"]),
    dessert: hasWord(text + " " + cat, ["trang mieng", "che"]),
    fried: hasWord(text, ["chien", "cha gio"]),
    warm: hasWord(text, ["lau", "sup", "canh", "nuong", "kho", "sot"]),
  };
}

function mealContext() {
  var chosenNames = currentMealNames();
  var hour = new Date().getHours();
  var chosenCount = Object.keys(chosenNames).length;
  return {
    chosenNames: chosenNames,
    chosenCount: chosenCount,
    hasChosen: chosenCount > 0,
    timeTone: hour >= 17 || hour < 10 ? "warm" : "light",
    intent: parseSuggestIntent(),
  };
}

function scoreItem(item, context, profile, picked) {
  var s = itemSignals(item);
  var score = 10;
  var reasons = [];
  var categoryCount = picked.categoryCount[s.category] || 0;

  if (itemAlreadyChosen(item, context.chosenNames)) {
    score -= 80;
    reasons.push("đã có trong lượt gọi này");
  }

  if (profile.mode === "light") {
    if (s.green || s.soup || s.drink || s.dessert) score += 18;
    if (s.fried || s.main) score -= 10;
  } else if (profile.mode === "warm") {
    if (s.warm || s.soup || s.main || s.topping) score += 17;
    if (s.drink || s.dessert) score -= 5;
  } else if (profile.mode === "fast") {
    if (s.starter || s.green || s.drink || s.topping) score += 16;
    if (s.soup || s.main) score -= 5;
  } else {
    if (s.starter || s.main || s.green || s.topping || s.soup) score += 10;
  }

  if (profile.stage === "start") {
    if (s.starter) {
      score += 14;
      reasons.push("mở vị nhẹ trước khi gọi món nặng");
    }
    if (s.main && picked.main === 0) {
      score += 8;
      reasons.push("giữ một món chắc bụng cho bàn");
    }
    if (s.green && picked.green === 0) {
      score += 8;
      reasons.push("cân lại rau xanh ngay từ đầu");
    }
  } else if (profile.stage === "next") {
    if (s.green || s.topping || s.soup) {
      score += 16;
      reasons.push("role match");
    }
    if (s.main && picked.main > 0) score -= 7;
  } else if (profile.stage === "finish") {
    if (s.drink || s.dessert || s.green) {
      score += 20;
      reasons.push("hợp để kết bữa nhẹ");
    }
    if (s.main || s.fried) score -= 18;
  }

  if (profile.people >= 4 && (s.topping || s.main || s.soup)) {
    score += 8;
    reasons.push("dễ chia cho bàn đông");
  }
  if (profile.people <= 2 && s.soup && picked.soup > 0) score -= 8;
  if (context.timeTone === "warm" && (s.warm || s.soup)) score += 5;
  if (context.timeTone === "light" && (s.green || s.drink)) score += 4;

  var intent = context.intent || { positive: [], negative: [] };
  intent.positive.forEach(function (key) {
    if (
      (key === "lau" && s.soup) ||
      (key === "rau" && s.green) ||
      (key === "nam" && s.text.indexOf("nam") !== -1) ||
      (key === "dauhu" && hasWord(s.text, ["dau hu", "tau hu"])) ||
      (key === "nhanh" && (s.starter || s.green || s.topping || s.drink)) ||
      (key === "nong" && (s.warm || s.soup)) ||
      (key === "nhe" && (s.green || s.drink || s.dessert)) ||
      (key === "cay" && s.text.indexOf("cay") !== -1) ||
      (key === "chien" && s.fried) ||
      (key === "no" && s.main) ||
      (key === "ngot" && s.dessert)
    ) {
      score += 18;
      reasons.unshift("khớp gu bạn vừa nhập");
    }
  });
  intent.negative.forEach(function (key) {
    if (
      (key === "lau" && s.soup) ||
      (key === "rau" && s.green) ||
      (key === "nhanh" && (s.soup || s.main)) ||
      (key === "nong" && (s.warm || s.soup)) ||
      (key === "nhe" && (s.fried || s.main)) ||
      (key === "cay" && s.text.indexOf("cay") !== -1) ||
      (key === "chien" && s.fried) ||
      (key === "no" && s.main) ||
      (key === "ngot" && s.dessert)
    ) {
      score -= 28;
      reasons.unshift("đã giảm vì không hợp gu bạn nhập");
    }
  });

  score -= categoryCount * 13;
  if (categoryCount === 0) reasons.push("giúp thực đơn không bị trùng nhóm");

  var novelty =
    Math.sin(
      (Number(item.id || 0) + 1) * (suggestionNonce + profile.variant * 7 + 3),
    ) * 6;
  score += novelty;

  if (!reasons.length) {
    if (s.soup) reasons.push("làm mềm nhịp ăn");
    else if (s.topping) reasons.push("thêm chất và dễ ăn chung");
    else if (s.drink) reasons.push("cân vị giữa các món");
    else reasons.push("phù hợp với gu đang chọn");
  }

  return { item: item, score: score, reasons: reasons, signals: s };
}

function targetSuggestionSize(people, stage) {
  if (people <= 1) return 3;
  if (people <= 2) return 4;
  if (people <= 4) return 5;
  return 6;
}

function makePickedState() {
  return { ids: {}, categoryCount: {}, main: 0, green: 0, soup: 0 };
}

function rememberPicked(picked, scored) {
  var item = scored.item;
  var s = scored.signals;
  picked.ids[item.id] = true;
  picked.categoryCount[s.category] =
    (picked.categoryCount[s.category] || 0) + 1;
  if (s.main) picked.main++;
  if (s.green) picked.green++;
  if (s.soup) picked.soup++;
}

function roleMatches(signals, role) {
  if (role === "starter") return signals.starter;
  if (role === "main") return signals.main;
  if (role === "green") return signals.green;
  if (role === "soup") return signals.soup;
  if (role === "topping") return signals.topping;
  if (role === "drink") return signals.drink;
  if (role === "dessert") return signals.dessert;
  if (role === "light")
    return signals.green || signals.soup || signals.drink || signals.dessert;
  if (role === "warm")
    return signals.soup || signals.warm || signals.main || signals.topping;
  if (role === "fast")
    return signals.starter || signals.green || signals.topping || signals.drink;
  return true;
}

function suggestionRoles(profile, context) {
  var roles = [];

  if (profile.stage === "next") {
    roles = ["green", "topping", "soup"];
    if (profile.people >= 3) roles.push("main");
    roles.push("drink");
  } else if (profile.mode === "light") {
    roles = ["green", "soup", "topping"];
    if (profile.people >= 2) roles.push("drink");
    if (profile.people >= 4) roles.push("main");
  } else if (profile.mode === "warm") {
    roles = ["soup", "topping", "green"];
    if (profile.people >= 2) roles.push("main");
    if (profile.people >= 4) roles.push("topping");
  } else if (profile.mode === "fast") {
    roles = ["starter", "green", "topping"];
    if (profile.people >= 2) roles.push("drink");
    if (profile.people >= 4) roles.push("main");
  } else {
    roles = ["starter", "main", "green", "topping"];
    if (profile.people >= 3) roles.push("soup");
    if (profile.people >= 5) roles.push("drink");
  }

  var intent = context.intent || { positive: [] };
  if (intent.positive.indexOf("lau") !== -1 && roles.indexOf("soup") === -1)
    roles.unshift("soup");
  if (intent.positive.indexOf("rau") !== -1 && roles.indexOf("green") === -1)
    roles.unshift("green");
  if (intent.positive.indexOf("no") !== -1 && roles.indexOf("main") === -1)
    roles.unshift("main");
  if (intent.positive.indexOf("ngot") !== -1) roles.push("dessert");
  if (intent.positive.indexOf("nhanh") !== -1)
    roles = roles.map(function (role) {
      return role === "soup" ? "fast" : role;
    });

  return roles.slice(0, targetSuggestionSize(profile.people, profile.stage));
}

function suggestionTitle(profile, variant) {
  if (profile.mode === "light") return "Gợi ý thanh nhẹ";
  if (profile.mode === "warm") return "Gợi ý món ấm nóng";
  if (profile.mode === "fast") return "Gợi ý ra nhanh";
  if (profile.stage === "next") return "Gợi ý gọi tiếp";
  return "Gợi ý cân bằng";
}

function buildSuggestionVariant(profile, context, variant) {
  var targetCount = targetSuggestionSize(profile.people, profile.stage);
  var picked = makePickedState();
  var roles = suggestionRoles(profile, context);
  var scoredPool = (MENU_ITEMS || [])
    .filter(function (item) {
      return item && Number(item.con_mon || 1) !== 0;
    })
    .map(function (item) {
      var localProfile = Object.assign({}, profile, { variant: variant });
      return scoreItem(item, context, localProfile, picked);
    });
  var selected = [];

  roles.forEach(function (role, index) {
    var ranked = scoredPool
      .map(function (scored) {
        var rescored = scoreItem(
          scored.item,
          context,
          Object.assign({}, profile, { variant: variant + index }),
          picked,
        );
        if (!roleMatches(rescored.signals, role)) rescored.score -= 45;
        if (picked.ids[rescored.item.id]) rescored.score -= 100;
        return rescored;
      })
      .filter(function (scored) {
        return !picked.ids[scored.item.id] && scored.score > -60;
      })
      .sort(function (a, b) {
        return b.score - a.score;
      });

    if (!ranked.length) return;
    var offset = Math.min(variant % 3, ranked.length - 1);
    var choice = ranked[offset];
    selected.push(choice);
    rememberPicked(picked, choice);
  });

  while (selected.length < targetCount) {
    var fallback = scoredPool
      .map(function (scored) {
        return scoreItem(
          scored.item,
          context,
          Object.assign({}, profile, { variant: variant }),
          picked,
        );
      })
      .filter(function (scored) {
        return !picked.ids[scored.item.id] && scored.score > -60;
      })
      .sort(function (a, b) {
        return b.score - a.score;
      })[0];
    if (!fallback) break;
    selected.push(fallback);
    rememberPicked(picked, fallback);
  }

  return {
    title: suggestionTitle(profile, variant),
    mood: "Theo cấu trúc bữa ăn",
    items: selected.map(function (scored) {
      return Object.assign({}, scored.item);
    }),
  };
}

function buildComboSets() {
  if (typeof MENU_ITEMS === "undefined" || !MENU_ITEMS.length) return [];
  var profile = {
    mode: activeSuggestMode(),
    stage: activeSuggestStage(),
    people: suggestPeopleCount(),
  };
  var context = mealContext();

  return [buildSuggestionVariant(profile, context, suggestionNonce % 6)];
}

function shuffleSuggestions() {
  suggestionNonce++;
  renderComboSets();
}

function renderComboSets() {
  var box = document.getElementById("comboList");
  if (!box) return;

  var sets = buildComboSets();
  if (!sets.length) {
    document.getElementById("comboSuggest").style.display = "none";
    return;
  }

  box.innerHTML = sets
    .map(function (set, index) {
      var itemsHtml = set.items
        .map(function (item) {
          var category = item.category || item.danh_muc || "";
          return (
            '<div class="combo-dish">' +
            '<div class="combo-dish-main"><strong>' +
            esc(menuItemName(item)) +
            "</strong>" +
            (category ? "<small>" + esc(category) + "</small>" : "") +
            "</div>" +
            "</div>"
          );
        })
        .join("");
      return (
        '<article class="combo-card">' +
        '<div class="combo-card-top"><span>' +
        esc(set.mood || "Gợi ý") +
        "</span></div>" +
        "<h3>" +
        esc(set.title) +
        "</h3>" +
        '<div class="combo-items">' +
        itemsHtml +
        "</div>" +
        '<button type="button" onclick="addComboSet(' +
        index +
        ')">+ Thêm gợi ý này</button>' +
        "</article>"
      );
    })
    .join("");
  window.__comboSets = sets;
}

function addComboSet(index) {
  var set = window.__comboSets && window.__comboSets[index];
  if (!set) return;

  set.items.forEach(function (item) {
    var name = menuItemName(item);
    var existing = cart.find(function (c) {
      return String(c.id) === String(item.id) && c.note === "";
    });
    if (existing) existing.qty = Math.min(10, existing.qty + 1);
    else cart.push({ id: item.id, name: name, qty: 1, note: "" });
  });

  renderCart();
  toast("Đã thêm " + set.title + " vào danh sách");
  openOrderPanel();
}
